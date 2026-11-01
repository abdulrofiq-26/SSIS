# scripts/resnet_service.py
#
# Python upstream service (Flask) untuk fitur CBIR (Content-Based Image Retrieval).
#
# PERBEDAAN UTAMA dengan index_vector.py / search_vector.py lama:
#   - Model ResNet50 dan koneksi ChromaDB dimuat SEKALI saat service start.
#   - Setiap request /index dan /search hanya melakukan ekstraksi 1 gambar (<1 detik),
#     TIDAK lagi cold-start TensorFlow + load bobot 100MB per request.
#
# Cara menjalankan (dari root proyek SSIS):
#   venv-tf\Scripts\python.exe scripts\resnet_service.py
# Service akan mendengarkan di http://127.0.0.1:5005
#
# Endpoint:
#   GET  /health                       -> {"status": "ok", "indexed": <n>}
#   POST /index   {image_path, sparepart_id} -> {"success": true}
#   POST /search  {image_path, n_results}    -> {"success": true, "ids": [...]}
#   POST /delete  {sparepart_id}             -> {"success": true}

import os
import sys

# REK 3: Perbaikan environment Python.
# PYTHONHASHSEED harus di-set SEBELUM interpreter benar-benar memakai random,
# namun bila di-set dari Laravel sering gagal ("failed to get random numbers").
# Set juga TF_ENABLE_ONEDNN_OPTS agar konsisten, dan matikan log TF yang berisik.
os.environ.setdefault("PYTHONHASHSEED", "0")
os.environ.setdefault("TF_ENABLE_ONEDNN_OPTS", "0")
os.environ.setdefault("TF_CPP_MIN_LOG_LEVEL", "2")

# Pastikan HOME/USERPROFILE valid (penyebab "failed to get random numbers" di Windows
# saat env diteruskan sebagian dari PHP).
if not os.environ.get("USERPROFILE") or not os.path.isdir(os.environ.get("USERPROFILE", "")):
    os.environ["USERPROFILE"] = os.path.expanduser("~")
if not os.environ.get("SystemRoot"):
    os.environ["SystemRoot"] = r"C:\Windows"
if not os.environ.get("SystemDrive"):
    os.environ["SystemDrive"] = "C:"

from flask import Flask, request, jsonify

import chromadb
import numpy as np
from tensorflow.keras.applications.resnet50 import ResNet50, preprocess_input
from tensorflow.keras.preprocessing import image as keras_image

app = Flask(__name__)

# --- Inisialisasi SEKALI: Model & ChromaDB ---
print("[resnet_service] Memuat model ResNet50 (sekali saja, mohon tunggu)...", flush=True)
MODEL = ResNet50(weights="imagenet", include_top=False, pooling="avg")
print("[resnet_service] Model ResNet50 siap.", flush=True)

PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
DB_PATH = os.path.join(PROJECT_ROOT, "storage", "app", "chroma_db")

CLIENT = chromadb.PersistentClient(path=DB_PATH)
COLLECTION = CLIENT.get_or_create_collection(
    "sparepart_images",
    metadata={"hnsw:space": "cosine"},
)
print(f"[resnet_service] ChromaDB siap. Jumlah vektor saat ini: {COLLECTION.count()}", flush=True)


def extract_features(img_path):
    """Ekstrak vektor fitur 2048-dimensi dari sebuah gambar."""
    img = keras_image.load_img(img_path, target_size=(224, 224))
    img_array = keras_image.img_to_array(img)
    expanded = np.expand_dims(img_array, axis=0)
    preprocessed = preprocess_input(expanded)
    features = MODEL.predict(preprocessed, verbose=0).flatten()

    # L2-normalisasi: panjang vektor = 1, sehingga cosine similarity stabil
    # dan hasil antar gambar dapat dibandingkan secara adil.
    norm = np.linalg.norm(features)
    if norm > 0:
        features = features / norm
    return features


@app.get("/health")
def health():
    return jsonify({"status": "ok", "indexed": COLLECTION.count()})


@app.post("/index")
def index_image():
    data = request.get_json(silent=True) or {}
    image_path = data.get("image_path")
    sparepart_id = data.get("sparepart_id")

    if not image_path or sparepart_id is None:
        return jsonify({"success": False, "message": "image_path dan sparepart_id wajib diisi."}), 400

    if not os.path.isfile(image_path):
        return jsonify({"success": False, "message": f"File tidak ditemukan: {image_path}"}), 400

    try:
        vector = extract_features(image_path)
        COLLECTION.upsert(embeddings=[vector.tolist()], ids=[str(sparepart_id)])
        return jsonify({
            "success": True,
            "message": f"Vektor sparepart {sparepart_id} berhasil diindex.",
            "indexed": COLLECTION.count(),
        })
    except Exception as e:
        print(f"[resnet_service] /index error: {e}", file=sys.stderr, flush=True)
        return jsonify({"success": False, "message": str(e)}), 500


@app.post("/search")
def search_image():
    data = request.get_json(silent=True) or {}
    image_path = data.get("image_path")
    n_results = int(data.get("n_results", 5))

    if not image_path:
        return jsonify({"success": False, "message": "image_path wajib diisi.", "ids": []}), 400

    if not os.path.isfile(image_path):
        return jsonify({"success": False, "message": f"File tidak ditemukan: {image_path}", "ids": []}), 400

    try:
        total_items = COLLECTION.count()
        if total_items == 0:
            return jsonify({"success": True, "ids": [], "message": "Belum ada data terindeks."})

        query_vector = extract_features(image_path)
        actual_n = min(n_results, total_items)

        results = COLLECTION.query(
            query_embeddings=[query_vector.tolist()],
            n_results=actual_n,
        )
        ids = results["ids"][0] if results and results.get("ids") else []

        # Hitung skor kemiripan (0-100%) dari jarak Cosine.
        # ChromaDB default memakai jarak Cosine: distance = 1 - cosine_similarity.
        # similarity = 1 - distance  ->  persentase = similarity * 100.
        distances = results.get("distances", [[]])[0] if results else []
        scores = []
        for i, _ in enumerate(ids):
            try:
                dist = float(distances[i])
            except (IndexError, TypeError, ValueError):
                dist = 1.0
            sim = max(0.0, min(1.0, 1.0 - dist))
            scores.append(round(sim * 100, 1))

        # Urutkan dari PALING MIRIP (skor tertinggi) ke paling rendah.
        paired = sorted(zip(ids, scores), key=lambda p: p[1], reverse=True)
        ids = [p[0] for p in paired]
        scores = [p[1] for p in paired]

        return jsonify({"success": True, "ids": ids, "scores": scores})
    except Exception as e:
        print(f"[resnet_service] /search error: {e}", file=sys.stderr, flush=True)
        return jsonify({"success": False, "message": str(e), "ids": []}), 500


@app.post("/delete")
def delete_vector():
    data = request.get_json(silent=True) or {}
    sparepart_id = data.get("sparepart_id")

    if sparepart_id is None:
        return jsonify({"success": False, "message": "sparepart_id wajib diisi."}), 400

    try:
        COLLECTION.delete(ids=[str(sparepart_id)])
        return jsonify({"success": True, "message": f"Vektor {sparepart_id} dihapus.", "indexed": COLLECTION.count()})
    except Exception as e:
        print(f"[resnet_service] /delete error: {e}", file=sys.stderr, flush=True)
        return jsonify({"success": False, "message": str(e)}), 500


if __name__ == "__main__":
    # host 127.0.0.1 saja (lokal), port 5005. threaded=True agar bisa melayani
    # beberapa request berurutan tanpa saling memblokir.
    app.run(host="127.0.0.1", port=5005, threaded=True, debug=False)
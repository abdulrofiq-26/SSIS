import sys
import json
import cv2
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import pickle
import os

# --- Fungsi Ekstraksi Fitur (Contoh Sederhana: Histogram) ---
def extract_feature_vector(image_path):
    # Baca gambar
    image = cv2.imread(image_path)
    if image is None:
        return None

    # Ubah ke ruang warna HSV (lebih baik untuk deskripsi warna)
    hsv = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)
    
    # Hitung histogram untuk channel H, S, V
    # ranges: [0, 180], [0, 256], [0, 256]
    hist = cv2.calcHist([hsv], [0, 1, 2], None, [8, 8, 8], [0, 180, 0, 256, 0, 256])
    
    # Normalisasi
    cv2.normalize(hist, hist)
    
    # Flatten/ratakan histogram menjadi vektor 1D
    return hist.flatten()

# --- Fungsi Pencarian Utama ---
def run_image_search(query_image_path, metadata_json_path, top_k=10):
    
    # 1. Ekstrak Vektor dari Gambar Query
    query_vector = extract_feature_vector(query_image_path)
    if query_vector is None:
        # Jika gambar query tidak bisa diproses, keluar dengan error
        print("Error: Gagal memproses gambar query.", file=sys.stderr)
        sys.exit(1)

    # 2. Baca Metadata Produk
    try:
        with open(metadata_json_path, 'r') as f:
            metadata = json.load(f)
    except Exception as e:
        print(f"Error: Gagal membaca metadata. {e}", file=sys.stderr)
        sys.exit(1)
    
    similarities = []
    
    # 3. Bandingkan Vektor Gambar Query dengan Vektor Database
    for item in metadata:
        product_id = item['id']
        vector_path = item['vector_path']
        
        # Cek apakah file vektor ada
        if not os.path.exists(vector_path):
            continue 

        try:
            # Muat vektor produk dari file .pkl
            with open(vector_path, 'rb') as f:
                db_vector = pickle.load(f)
            
            # Hitung Cosine Similarity
            # reshape(-1) mengubah array menjadi 1D agar bisa dihitung
            similarity = cosine_similarity(query_vector.reshape(1, -1), db_vector.reshape(1, -1))[0][0]
            
            similarities.append({'id': product_id, 'score': similarity})
            
        except Exception as e:
            # Log error muat vektor
            # print(f"Error loading vector for ID {product_id}: {e}", file=sys.stderr)
            continue
            
    # 4. Urutkan berdasarkan skor kemiripan (tertinggi ke terendah)
    similarities.sort(key=lambda x: x['score'], reverse=True)
    
    # 5. Ambil Top-K ID
    top_ids = [item['id'] for item in similarities[:top_k]]
    
    # 6. Cetak hasil dalam format JSON (akan ditangkap oleh Laravel)
    print(json.dumps(top_ids))

if __name__ == "__main__":
    if len(sys.argv) != 3:
        # Harusnya menerima path gambar dan path metadata JSON
        print("Usage: python3 image_search.py <query_image_path> <metadata_json_path>", file=sys.stderr)
        sys.exit(1)
        
    query_image_path = sys.argv[1]
    metadata_json_path = sys.argv[2]
    
    run_image_search(query_image_path, metadata_json_path)
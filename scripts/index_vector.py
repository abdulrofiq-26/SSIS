# scripts/index_vector.py

import sys
import chromadb
from PIL import Image
import tensorflow as tf
from tensorflow.keras.applications.resnet50 import ResNet50, preprocess_input
from tensorflow.keras.preprocessing import image as keras_image
import numpy as np
import os

# --- Inisialisasi Model & Database ---
# Muat model ResNet50 yang sudah dilatih di ImageNet, tanpa lapisan klasifikasi atas.
model = ResNet50(weights='imagenet', include_top=False, pooling='avg')

# Inisialisasi ChromaDB Client.
# Ini akan membuat folder database di dalam proyek Anda jika belum ada.
project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
db_path = os.path.join(project_root, 'storage', 'app', 'chroma_db') # <-- Path BARU di storage/app/
client = chromadb.PersistentClient(path=db_path)

# Dapatkan atau buat "collection" (seperti tabel di database biasa)
# Jika sudah ada, ia akan menggunakannya; jika tidak, ia akan membuatnya.
collection = client.get_or_create_collection("sparepart_images")

# --- Fungsi untuk Ekstraksi Fitur ---
def extract_features(img_path):
    try:
        # Muat dan siapkan gambar untuk model ResNet50
        img = keras_image.load_img(img_path, target_size=(224, 224))
        img_array = keras_image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)

        # Gunakan model untuk memprediksi dan mendapatkan vektor
        features = model.predict(preprocessed_img, verbose=0)
        return features.flatten()
    except Exception as e:
        print(f"Error processing image {img_path}: {e}", file=sys.stderr)
        return None

# --- Fungsi Utama ---
def main(image_path, sparepart_id):
    # Ubah gambar menjadi vektor
    vector = extract_features(image_path)
    
    if vector is not None:
        try:
            # Simpan atau perbarui vektor di ChromaDB (upsert mencegah error duplicate ID)
            collection.upsert(
                embeddings=[vector.tolist()],
                ids=[str(sparepart_id)] # ID harus dalam format string
            )
            print(f"Success: Vector for sparepart ID {sparepart_id} has been indexed.")
            return True
        except Exception as e:
            print(f"Error adding vector to ChromaDB: {e}", file=sys.stderr)
            return False
    else:
        return False

# --- Jalankan Skrip ---
if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python index_vector.py <image_path> <sparepart_id>", file=sys.stderr)
        sys.exit(1)
        
    img_path = sys.argv[1]
    sp_id = sys.argv[2]
    
    if main(img_path, sp_id):
        sys.exit(0)
    else:
        sys.exit(1)
# scripts/search_vector.py

import sys
import json
import chromadb
from PIL import Image
import tensorflow as tf
from tensorflow.keras.applications.resnet50 import ResNet50, preprocess_input
from tensorflow.keras.preprocessing import image as keras_image
import numpy as np
import os

# --- Inisialisasi Model & Database (Sama seperti di skrip indexing) ---
model = ResNet50(weights='imagenet', include_top=False, pooling='avg')

# KOREKSI: Inisialisasi ChromaDB Client dengan path absolut di dalam storage Laravel
project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
db_path = os.path.join(project_root, 'storage', 'app', 'chroma_db') # <-- Path BARU di storage/app/
client = chromadb.PersistentClient(path=db_path)

# Dapatkan collection yang sudah ada (tidak perlu membuat baru)
collection = client.get_collection("sparepart_images")

# --- Fungsi Ekstraksi Fitur (Sama seperti di skrip indexing) ---
def extract_features(img_path):
    try:
        img = keras_image.load_img(img_path, target_size=(224, 224))
        img_array = keras_image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        features = model.predict(preprocessed_img, verbose=0)
        return features.flatten()
    except Exception as e:
        print(f"Error processing image {img_path}: {e}", file=sys.stderr)
        return None

# --- Fungsi Utama ---
def main(query_image_path, n_results=5):
    # Ubah gambar query menjadi vektor
    query_vector = extract_features(query_image_path)

    if query_vector is not None:
        try:
            # Lakukan pencarian di ChromaDB
            results = collection.query(
                query_embeddings=[query_vector.tolist()],
                n_results=n_results # Berapa hasil teratas yang ingin kita dapatkan
            )
            
            # Ambil hanya ID dari hasil pencarian
            ids = results['ids'][0]
            
            # Cetak hasilnya dalam format JSON agar bisa dibaca Laravel
            print(json.dumps(ids))
            return True
        except Exception as e:
            print(f"Error querying ChromaDB: {e}", file=sys.stderr)
            return False
    else:
        return False

# --- Jalankan Skrip ---
if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python search_vector.py <query_image_path>", file=sys.stderr)
        sys.exit(1)
        
    query_path = sys.argv[1]
    
    if main(query_path):
        sys.exit(0)
    else:
        sys.exit(1)
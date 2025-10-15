# scripts/image_search.py
import sys
import json
import cv2
import numpy as np
import pickle
import os

# --- FUNGSI BARU: Menghitung Cosine Similarity dengan NumPy ---
def cosine_similarity_numpy(vec1, vec2):
    """Menghitung cosine similarity antara dua vektor menggunakan NumPy."""
    dot_product = np.dot(vec1, vec2)
    norm_vec1 = np.linalg.norm(vec1)
    norm_vec2 = np.linalg.norm(vec2)
    
    if norm_vec1 == 0 or norm_vec2 == 0:
        return 0.0
    
    return dot_product / (norm_vec1 * norm_vec2)

# --- Fungsi Ekstraksi Fitur (Tidak berubah) ---
def extract_feature_vector(image_path):
    image = cv2.imread(image_path)
    if image is None: return None
    try:
        hsv = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)
        hist = cv2.calcHist([hsv], [0, 1, 2], None, [8, 8, 8], [0, 180, 0, 256, 0, 256])
        cv2.normalize(hist, hist)
        return hist.flatten()
    except cv2.error as e:
        print(f"Error CV processing image {image_path}: {e}", file=sys.stderr)
        return None

# --- Fungsi Pencarian Utama (Dimodifikasi) ---
def run_image_search(query_image_path, metadata_json_path, top_k=10):
    query_vector = extract_feature_vector(query_image_path)
    if query_vector is None:
        print("Error: Gagal memproses gambar query.", file=sys.stderr)
        sys.exit(1)

    try:
        with open(metadata_json_path, 'r') as f:
            metadata = json.load(f)
    except Exception as e:
        print(f"Error: Gagal membaca metadata. {e}", file=sys.stderr)
        sys.exit(1)
    
    similarities = []
    
    for item in metadata:
        product_id = item['id']
        vector_path = item['vector_path']
        
        if not os.path.exists(vector_path): continue 

        try:
            with open(vector_path, 'rb') as f:
                db_vector = pickle.load(f)
            
            # DIGANTI: Gunakan fungsi NumPy kita, bukan sklearn
            similarity = cosine_similarity_numpy(query_vector, db_vector)
            
            similarities.append({'id': product_id, 'score': similarity})
            
        except Exception:
            continue
            
    similarities.sort(key=lambda x: x['score'], reverse=True)
    top_ids = [item['id'] for item in similarities[:top_k]]
    print(json.dumps(top_ids))

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python image_search.py <query_image_path> <metadata_json_path>", file=sys.stderr)
        sys.exit(1)
        
    query_image_path = sys.argv[1]
    metadata_json_path = sys.argv[2]
    
    run_image_search(query_image_path, metadata_json_path)
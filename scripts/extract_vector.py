# scripts/extract_vector.py

import sys
import cv2
import numpy as np
import pickle
import os

# --- Fungsi Ekstraksi Fitur (Menggunakan Histogram Warna) ---
def extract_and_save(image_path, output_vector_path):
    """
    Mengekstrak fitur visual (Histogram) dari gambar dan menyimpannya sebagai file pickle (.pkl).
    """
    # 1. Baca Gambar
    image = cv2.imread(image_path)
    if image is None:
        print(f"Error: Gagal memuat gambar dari path: {image_path}", file=sys.stderr)
        return False

    # 2. Ubah ke ruang warna HSV dan Hitung Histogram
    # Histogram adalah representasi visual yang baik untuk warna.
    hsv = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)
    hist = cv2.calcHist([hsv], [0, 1, 2], None, [8, 8, 8], [0, 180, 0, 256, 0, 256])
    
    # 3. Normalisasi dan Ratakan (Flatten)
    cv2.normalize(hist, hist)
    vector = hist.flatten()

    # 4. Simpan Vektor ke File Pickle (.pkl)
    try:
        os.makedirs(os.path.dirname(output_vector_path), exist_ok=True)
        with open(output_vector_path, 'wb') as f:
            pickle.dump(vector, f)
        return True
    except Exception as e:
        print(f"Error: Gagal menyimpan vektor ke {output_vector_path}. Error: {e}", file=sys.stderr)
        return False

# --- Jalankan Skrip ---
if __name__ == "__main__":
    if len(sys.argv) != 3:
        # Mengharapkan 2 argumen: path_gambar_input dan path_vektor_output
        print("Usage: python3 extract_vector.py <input_image_path> <output_vector_path>", file=sys.stderr)
        sys.exit(1)
        
    input_path = sys.argv[1]
    output_path = sys.argv[2]
    
    if extract_and_save(input_path, output_path):
        print("Success: Vector saved.")
        sys.exit(0)
    else:
        sys.exit(1)
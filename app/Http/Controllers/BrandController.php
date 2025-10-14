<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BrandsImport; // Tambahkan ini
use App\Exports\BrandsTemplateExport; // Tambahkan ini
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    

    public function index()
    {
        return view('brands.index', [
             'brand' => new Brand(), // Objek Brand kosong untuk inisialisasi form Offcanvas
        ]);
    }

    /**
     * Data untuk Datatables (Server-Side Processing).
     */
    public function data(Request $request)
    {
        // Pastikan query ini berhasil mengambil data dari model Brand
        $brands = Brand::select('id', 'nama_brand', 'logo_brand'); // Ambil hanya kolom yang dibutuhkan

        return DataTables::of($brands)
            // Filter opsional (sudah benar)
            ->filter(function ($query) use ($request) {
                if ($request->filled('search')) {
                    $query->where('nama_brand', 'like', '%' . $request->search['value'] . '%');
                }
            })
            // Tambahkan kolom ID yang akan kita gunakan untuk Edit/Delete (idd)
            ->addColumn('idd', function ($brands) {
                return $brands->id; 
            })
            // Tambahkan kolom 'action' (Harus ada meski isinya kosong, untuk diparsing di JS)
            ->addColumn('action', function ($brands) {
                return ''; 
            })
            // Kolom Logo (sudah benar)
            ->addColumn('logo_brand', function ($brands) {
                return $brands->logo_brand;
            })
            // Tentukan kolom mana yang bukan berasal dari database
            ->rawColumns(['idd', 'logo_brand', 'action']) 
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        $validatedData = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands,nama_brand',
            'logo_brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB
        ], [
            'nama_brand.unique' => 'Nama brand ini sudah ada.',
            'logo_brand.image' => 'File harus berupa gambar.',
        ]);

        // 2. Proses Upload Gambar (Logo)
        if ($request->hasFile('logo_brand')) {
            // Simpan gambar di folder 'public/logos'
            // Gunakan Storage::disk('public') untuk menyimpan di public/storage
            $path = $request->file('logo_brand')->store('logos', 'public');
            $validatedData['logo_brand'] = $path;
        }

        // 3. Simpan ke Database
        Brand::create($validatedData);

        return redirect()->route('brands.index')->with('success', 'Brand baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        // Biasanya untuk detail, tapi kita abaikan karena data ditampilkan di index
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        // Mengirimkan data brand yang spesifik ke view edit.
        return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        // Hapus blok try-catch yang membawa failed_update_brand_id
        
        // 1. Validasi Data
        $validatedData = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands,nama_brand,' . $brand->id,
            'logo_brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
        ]);
        
        // 2. Proses Upload Gambar/Logo Baru
        if ($request->hasFile('logo_brand')) {
            // Hapus logo lama jika ada
            if ($brand->logo_brand && Storage::disk('public')->exists($brand->logo_brand)) {
                Storage::disk('public')->delete($brand->logo_brand);
            }
            
            // Simpan logo baru
            $path = $request->file('logo_brand')->store('logos', 'public');
            $validatedData['logo_brand'] = $path;
        }

        // 3. Update ke Database
        $brand->update($validatedData);

        // Redirect ke index setelah sukses.
        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    /**
     * Menghapus brand menggunakan AJAX (dipanggil dari SweetAlert).
     */
    public function delete(Request $request)
    {
        $id = $request->id;
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand tidak ditemukan.'], 404);
        }

        try {
            // Hapus logo dari storage jika ada
            if ($brand->logo_brand && Storage::disk('public')->exists($brand->logo_brand)) {
                Storage::disk('public')->delete($brand->logo_brand);
            }
            
            // Hapus record dari database
            $brand->delete();

            return response()->json(['success' => true, 'message' => 'Brand berhasil dihapus.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus brand: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import data brand dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        
        try {
            Excel::import(new BrandsImport, $request->file('file'));

            return redirect()->route('brands.index')->with('success', 'Import data brand berhasil dilakukan!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            
            return redirect()->route('brands.index')
                             ->with('error', 'Gagal impor! Terdapat ' . count($failures) . ' kesalahan validasi.')
                             ->with('import_errors', $errors);
        } catch (\Exception $e) {
            return redirect()->route('brands.index')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menghasilkan file Excel template untuk diunduh.
     */
    public function downloadTemplate()
    {
        $filename = 'template_import_brand_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new BrandsTemplateExport, $filename);
    }

}

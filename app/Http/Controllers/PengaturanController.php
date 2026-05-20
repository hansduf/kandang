<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = Pengaturan::all();
        return view('pengaturan.index', compact('settings'));
    }

    public function edit(Pengaturan $pengaturan)
    {
        return view('pengaturan.edit', compact('pengaturan'));
    }

    public function update(Request $request, Pengaturan $pengaturan)
    {
        // Validate based on tipe_data
        $rules = ['nilai' => 'required'];
        
        if ($pengaturan->tipe_data === 'integer') {
            $rules['nilai'] = 'required|integer|min:1';
        } elseif ($pengaturan->tipe_data === 'float') {
            $rules['nilai'] = 'required|numeric|min:0.01';
        } elseif ($pengaturan->tipe_data === 'boolean') {
            $rules['nilai'] = 'required|in:0,1';
        } else {
            $rules['nilai'] = 'required|string|max:255';
        }
        
        $validated = $request->validate($rules);

        try {
            $pengaturan->update([
                'nilai' => $validated['nilai'],
            ]);

            return redirect()->route('pengaturan.index')
                             ->with('success', 'Pengaturan " ' . ucwords(str_replace('_', ' ', $pengaturan->kunci)) . ' " berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage())
                             ->withInput();
        }
    }
}

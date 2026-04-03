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
        $request->validate([
            'nilai' => 'required|string',
        ]);

        $pengaturan->update([
            'nilai' => $request->nilai,
        ]);

        return redirect()->route('pengaturan.index')
                         ->with('success', 'Pengaturan berhasil diperbarui!');
    }
}

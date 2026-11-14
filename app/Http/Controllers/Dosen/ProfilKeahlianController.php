<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\BidangKeahlian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfilKeahlianController extends Controller
{
    public function index()
    {
        /** @var User|null $dosen */
        $dosen = Auth::user();

        abort_unless($dosen && $dosen->role === 'dosen', 403);

        $selectedTags = $dosen->keahlian()
            ->pluck('nama_bidang')
            ->toArray();

        $availableTags = BidangKeahlian::orderBy('nama_bidang')
            ->pluck('nama_bidang')
            ->toArray();

        return view('dosen.profil-keahlian', [
            'dosen' => $dosen,
            'profileName' => $dosen->name,
            'studyProgram' => $dosen->program_studi,
            'expertiseDescription' => $dosen->deskripsi_keahlian,
            'profilePhoto' => $dosen->foto_profil,
            'selectedTags' => $selectedTags,
            'availableTags' => $availableTags,
        ]);
    }

    public function update(Request $request)
    {
        /** @var User|null $dosen */
        $dosen = Auth::user();

        abort_unless($dosen && $dosen->role === 'dosen', 403);

        // Normalisasi tag sebelum validasi.
        $tags = $request->input('tags', []);

        if (is_array($tags)) {
            $tags = array_map(function ($tag) {
                return is_string($tag)
                    ? trim(preg_replace('/\s+/', ' ', $tag))
                    : $tag;
            }, $tags);
        }

        $request->merge([
            'tags' => $tags,
        ]);

        // Validasi data profil, tag, foto, dan permintaan hapus foto.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'program_studi' => ['required', 'string', 'max:255'],
            'deskripsi_keahlian' => ['required', 'string'],

            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => [
                'required',
                'string',
                'max:100',
                'distinct:ignore_case',
            ],

            'foto_profil' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'hapus_foto_profil' => [
                'nullable',
                'boolean',
            ],
        ], [
            'tags.required' => 'Minimal pilih satu bidang keahlian.',
            'tags.min' => 'Minimal pilih satu bidang keahlian.',
            'tags.*.required' => 'Tag keahlian tidak boleh kosong.',
            'tags.*.distinct' => 'Tag keahlian tidak boleh duplikat.',

            'foto_profil.image' => 'File harus berupa gambar.',
            'foto_profil.mimes' => 'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto_profil.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $oldPhotoPath = $dosen->foto_profil;
        $newPhotoPath = null;

        // Jika foto baru diunggah, proses upload lebih diprioritaskan
        // daripada permintaan hapus foto.
        $shouldDeletePhoto = $request->boolean('hapus_foto_profil')
            && !$request->hasFile('foto_profil');

        try {
            // Simpan foto baru jika dosen mengunggah foto.
            if ($request->hasFile('foto_profil')) {
                $newPhotoPath = $request->file('foto_profil')
                    ->store('foto-profil', 'public');

                if (!$newPhotoPath) {
                    throw new \RuntimeException(
                        'Foto profil gagal disimpan.'
                    );
                }
            }

            // Simpan profil dan tag dalam satu transaksi database.
            DB::transaction(function () use (
                $dosen,
                $validated,
                $newPhotoPath,
                $shouldDeletePhoto
            ) {
                $tagIds = [];

                foreach ($validated['tags'] as $namaTag) {
                    $tag = BidangKeahlian::firstOrCreate([
                        'nama_bidang' => $namaTag,
                    ]);

                    $tagIds[] = $tag->bidang_keahlian_id;
                }

                $profileData = [
                    'name' => $validated['name'],
                    'program_studi' => $validated['program_studi'],
                    'deskripsi_keahlian' => $validated['deskripsi_keahlian'],
                ];

                // Perbarui lokasi foto jika ada foto baru.
                if ($newPhotoPath !== null) {
                    $profileData['foto_profil'] = $newPhotoPath;
                }

                // Kosongkan foto profil jika dosen memilih hapus foto.
                elseif ($shouldDeletePhoto) {
                    $profileData['foto_profil'] = null;
                }

                $dosen->update($profileData);

                // Simpan tag baru dan lepaskan tag yang dihapus.
                $dosen->keahlian()->sync($tagIds);
            });

            // Hapus foto lama setelah transaksi database berhasil.
            if (
                $oldPhotoPath &&
                (
                    $newPhotoPath !== null ||
                    $shouldDeletePhoto
                ) &&
                $oldPhotoPath !== $newPhotoPath
            ) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

        } catch (Throwable $exception) {
            // Hapus foto baru jika proses penyimpanan gagal.
            if ($newPhotoPath !== null) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('dosen.profil-keahlian')
            ->with(
                'success',
                $shouldDeletePhoto
                    ? 'Profil berhasil diperbarui dan foto profil berhasil dihapus.'
                    : 'Profil dan bidang keahlian berhasil diperbarui.'
            );
    }
}

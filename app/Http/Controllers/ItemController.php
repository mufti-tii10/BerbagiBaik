<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public const CONDITIONS = ['Baru', 'Seperti baru', 'Baik', 'Cukup baik'];

    public function index(Request $request)
    {
        $items = $request->user()->items()->with('category')->latest()->get();

        return view('donor.items.index', compact('items'));
    }

    public function create()
    {
        return view('donor.items.create', [
            'categories' => Category::orderBy('name')->get(),
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);

        $data['photo_url'] = $request->file('photo')->store('items', 'public');
        unset($data['photo']);
        $data['donor_id'] = $request->user()->id;
        $data['status'] = 'pending'; // menunggu verifikasi admin

        Item::create($data);

        return redirect()->route('donor.items.index')
            ->with('success', 'Barang berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function edit(Request $request, Item $item)
    {
        $this->authorizeOwner($request, $item);

        return view('donor.items.edit', [
            'item' => $item,
            'categories' => Category::orderBy('name')->get(),
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeOwner($request, $item);

        $data = $this->validated($request, false);

        if ($request->hasFile('photo')) {
            if ($item->photo_url) {
                Storage::disk('public')->delete($item->photo_url);
            }
            $data['photo_url'] = $request->file('photo')->store('items', 'public');
        }
        unset($data['photo']);

        // Setelah diubah, barang diverifikasi ulang oleh admin
        $data['status'] = 'pending';
        $data['admin_note'] = null;

        $item->update($data);

        return redirect()->route('donor.items.index')
            ->with('success', 'Barang diperbarui dan dikirim ulang untuk verifikasi admin.');
    }

    public function destroy(Request $request, Item $item)
    {
        $this->authorizeOwner($request, $item);

        if ($item->photo_url) {
            Storage::disk('public')->delete($item->photo_url);
        }
        $item->delete();

        return redirect()->route('donor.items.index')->with('success', 'Barang dihapus.');
    }

    private function authorizeOwner(Request $request, Item $item): void
    {
        // Hanya pemilik yang boleh mengubah, dan barang yang sudah selesai dikunci
        abort_unless($item->donor_id === $request->user()->id, 403);
        abort_if($item->status === 'completed', 403, 'Barang yang sudah selesai tidak dapat diubah.');
    }

    private function validated(Request $request, bool $photoRequired): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'condition' => ['required', Rule::in(self::CONDITIONS)],
            'location' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => [$photoRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Pilih kategori.',
            'condition.required' => 'Pilih kondisi barang.',
            'location.required' => 'Lokasi wajib diisi.',
            'photo.required' => 'Foto barang wajib diunggah.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 2 MB.',
        ]);
    }
}

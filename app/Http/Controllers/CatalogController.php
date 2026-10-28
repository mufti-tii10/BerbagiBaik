<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $location = trim((string) $request->query('location', ''));
        $category = (string) $request->query('category', '');
        $condition = (string) $request->query('condition', '');
        $sort = (string) $request->query('sort', 'newest');

        if (! in_array($condition, ItemController::CONDITIONS, true)) {
            $condition = '';
        }
        if (! in_array($sort, ['newest', 'oldest', 'name'], true)) {
            $sort = 'newest';
        }
        if (! ctype_digit($category)) {
            $category = '';
        }

        $items = Item::available()
            ->with(['category', 'donor'])
            ->when($q !== '', function ($query) use ($q) {
                $like = $this->like($q);
                $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($location !== '', fn ($query) => $query->where('location', 'like', $this->like($location)))
            ->when($category !== '', fn ($query) => $query->where('category_id', $category))
            ->when($condition !== '', fn ($query) => $query->where('condition', $condition))
            ->when($sort === 'newest', fn ($query) => $query->latest('id'))
            ->when($sort === 'oldest', fn ($query) => $query->oldest('id'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')
            ->withCount(['items as available_count' => fn ($query) => $query->available()])
            ->get();

        $locations = Item::available()->distinct()->orderBy('location')->pluck('location');

        $filters = compact('q', 'location', 'category', 'condition', 'sort');
        $conditions = ItemController::CONDITIONS;

        return view('catalog.index', compact('items', 'categories', 'locations', 'filters', 'conditions'));
    }

    public function show(Request $request, Item $item)
    {
        $user = $request->user();
        $canPreview = $user && ($user->isAdmin() || $user->id === $item->donor_id);

        // Barang yang belum disetujui hanya boleh dilihat pemilik dan admin
        abort_unless($item->status === 'published' || $canPreview, 404);

        $item->load(['category', 'donor']);
        $reserved = $item->requests()->where('status', 'awaiting_handover')->exists();

        $myRequest = null;
        $activeCount = 0;
        if ($user && $user->isReceiver()) {
            $myRequest = $item->requests()->where('receiver_id', $user->id)->active()->first();
            $activeCount = $user->donationRequests()->active()->count();
        }

        return view('catalog.show', compact('item', 'reserved', 'canPreview', 'myRequest', 'activeCount'));
    }

    // Cegah karakter % dan _ dari pengguna bekerja sebagai wildcard
    private function like(string $value): string
    {
        return '%' . addcslashes($value, '%_\\') . '%';
    }
}

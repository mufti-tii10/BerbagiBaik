@csrf
<div class="field">
    <label for="name">Nama barang</label>
    <input id="name" name="name" value="{{ old('name', $item?->name) }}" required>
    @error('name') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="category_id">Kategori</label>
    <select id="category_id" name="category_id" required>
        <option value="">-- pilih --</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $item?->category_id) == $cat->id)>{{ $cat->name }}</option>
        @endforeach
    </select>
    @error('category_id') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="condition">Kondisi</label>
    <select id="condition" name="condition" required>
        <option value="">-- pilih --</option>
        @foreach ($conditions as $c)
            <option value="{{ $c }}" @selected(old('condition', $item?->condition) === $c)>{{ $c }}</option>
        @endforeach
    </select>
    @error('condition') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="location">Lokasi (kota / kecamatan)</label>
    <input id="location" name="location" placeholder="Contoh: Lhokseumawe" value="{{ old('location', $item?->location) }}" required>
    @error('location') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="description">Deskripsi (opsional)</label>
    <textarea id="description" name="description" rows="4">{{ old('description', $item?->description) }}</textarea>
    @error('description') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="photo">Foto barang {{ $item ? '(kosongkan jika tidak diganti)' : '' }}</label>
    <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" data-preview="preview" {{ $item ? '' : 'required' }}>
    <small class="hint">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
    @error('photo') <div class="error">{{ $message }}</div> @enderror
    <img id="preview" class="preview" alt="Pratinjau foto"
         @if ($item && $item->photo_url) src="{{ asset('storage/' . $item->photo_url) }}" style="display:block" @else style="display:none" @endif>
</div>

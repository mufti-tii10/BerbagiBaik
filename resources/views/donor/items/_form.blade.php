@csrf
@php
    $lat = old('latitude', $item?->latitude);
    $lng = old('longitude', $item?->longitude);
@endphp

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
    <input type="hidden" id="latitude" name="latitude" value="{{ $lat }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ $lng }}">

    <div class="btn-row" style="margin-top:8px">
        <button type="button" id="use-location" class="btn btn-outline btn-small-dark">&#128205; Gunakan lokasi saya</button>
        <a id="map-link" target="_blank" rel="noopener"
           @if ($lat && $lng) href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" style="display:inline" @else style="display:none" @endif>
            Lihat di Google Maps
        </a>
        <button type="button" id="clear-location" class="link-btn">Hapus titik</button>
    </div>
    <small id="location-status" class="hint">Titik koordinat dipakai untuk membantu serah terima dan tidak ditampilkan di halaman publik.</small>
    @error('location') <div class="error">{{ $message }}</div> @enderror
    @error('latitude') <div class="error">{{ $message }}</div> @enderror
    @error('longitude') <div class="error">{{ $message }}</div> @enderror
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

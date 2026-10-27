document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-password');
    if (!btn) return;

    const input = document.getElementById(btn.dataset.target);
    if (!input) return;

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Sembunyikan sandi' : 'Tampilkan sandi');
});

// Pratinjau foto sebelum diunggah
document.addEventListener('change', function (e) {
    const input = e.target.closest('input[type=file][data-preview]');
    if (!input || !input.files[0]) return;

    const img = document.getElementById(input.dataset.preview);
    if (!img) return;

    img.src = URL.createObjectURL(input.files[0]);
    img.style.display = 'block';
});

// Lokasi terkini: ambil koordinat dari browser, lalu cari nama tempatnya
function showMapLink(la, lo) {
    const link = document.getElementById('map-link');
    if (!link) return;
    link.href = 'https://www.google.com/maps?q=' + la + ',' + lo;
    link.style.display = 'inline';
}

document.addEventListener('click', function (e) {
    const clear = e.target.closest('#clear-location');
    if (clear) {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('map-link').style.display = 'none';
        document.getElementById('location-status').textContent = 'Titik lokasi dihapus.';
        return;
    }

    const btn = e.target.closest('#use-location');
    if (!btn) return;

    const status = document.getElementById('location-status');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const textInput = document.getElementById('location');

    if (!navigator.geolocation) {
        status.textContent = 'Browser ini tidak mendukung lokasi. Isi lokasi secara manual.';
        return;
    }

    status.textContent = 'Mencari lokasi Anda...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(async function (pos) {
        const la = pos.coords.latitude.toFixed(7);
        const lo = pos.coords.longitude.toFixed(7);
        latInput.value = la;
        lngInput.value = lo;
        showMapLink(la, lo);

        try {
            const res = await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=14&accept-language=id&lat=' + la + '&lon=' + lo);
            if (!res.ok) throw new Error('gagal');
            const data = await res.json();
            const a = data.address || {};
            const area = a.village || a.suburb || a.city_district || a.neighbourhood || '';
            const city = a.city || a.town || a.county || a.regency || '';
            const name = [area, city].filter(Boolean).join(', ');

            if (name) {
                textInput.value = name;
                status.textContent = 'Lokasi terdeteksi. Anda boleh mengubah teksnya bila perlu.';
            } else {
                status.textContent = 'Titik tersimpan, tetapi nama tempat tidak terdeteksi. Isi lokasi secara manual.';
            }
        } catch (err) {
            status.textContent = 'Titik tersimpan, tetapi nama tempat gagal diambil. Isi lokasi secara manual.';
        }
        btn.disabled = false;
    }, function (err) {
        status.textContent = err.code === 1
            ? 'Izin lokasi ditolak. Izinkan lokasi di browser, atau isi lokasi secara manual.'
            : 'Lokasi tidak dapat diambil. Isi lokasi secara manual.';
        btn.disabled = false;
    }, { enableHighAccuracy: true, timeout: 15000 });
});

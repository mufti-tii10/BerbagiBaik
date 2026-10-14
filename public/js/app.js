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

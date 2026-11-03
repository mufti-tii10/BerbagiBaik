# Panduan Setup BerbagiBaik

Kebutuhan: PHP 8.4, Composer, Git, MySQL atau MariaDB.
(Windows: pakai Laragon Full dan pilih PHP 8.4.)

## Langkah

1. Clone dan pasang dependensi:

        git clone https://github.com/mufti-tii10/BerbagiBaik.git
        cd BerbagiBaik
        composer install
        cp .env.example .env
        php artisan key:generate

2. Buat database kosong bernama `berbagibaik`.
3. Buka `.env`, hapus tanda `#` pada baris DB_, lalu isi sesuai MySQL Anda:

        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=berbagibaik
        DB_USERNAME=root
        DB_PASSWORD=

4. Buat tabel dan data contoh, lalu jalankan:

        php artisan migrate --seed
        php artisan storage:link
        php artisan serve

5. Buka http://127.0.0.1:8000 (bukan 0.0.0.0, supaya lokasi di browser berfungsi).

## Akun contoh (hanya untuk pengembangan)

| Peran | Email | Sandi |
|---|---|---|
| Admin | admin@berbagibaik.test | ADMIN_PASSWORD di .env (bawaan: admin12345) |
| Donatur | donatur@berbagibaik.test | demo12345 |
| Donatur kedua | donatur2@berbagibaik.test | demo12345 |
| Penerima | penerima@berbagibaik.test | demo12345 |
| Penerima kedua | penerima2@berbagibaik.test | demo12345 |

## Aturan kerja kelompok

- Jangan commit file `.env`, `*.sql`, atau cadangan database.
- Sebelum mulai kerja: `git pull`. Setelah ada migration baru dari teman: `php artisan migrate`.
- Satu fitur satu cabang: `git checkout -b fitur/nama-fitur`, lalu push dan buat Pull Request.
- Jangan mengubah migration yang sudah di-push. Buat migration baru.
- Jangan membagikan token GitHub kepada siapa pun.

## Masalah umum

| Gejala | Solusi |
|---|---|
| Access denied saat migrate | Samakan user dan sandi di .env dengan MySQL Anda |
| Unknown database | Buat database berbagibaik terlebih dahulu |
| Foto tidak tampil | Jalankan php artisan storage:link |
| Halaman tanpa gaya | Tekan Ctrl+Shift+R |

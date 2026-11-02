<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Category;
use App\Models\DonationRequest;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Data contoh untuk pengembangan dan demo. Aman dijalankan berulang kali.
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $donor = $this->user('Donatur Demo', 'donatur@berbagibaik.test', 'donor', '6281200000001', 3);
        $donor2 = $this->user('Donatur Kedua', 'donatur2@berbagibaik.test', 'donor', '6281200000003');
        $receiver = $this->user('Penerima Demo', 'penerima@berbagibaik.test', 'receiver', '6281200000002');
        $receiver2 = $this->user('Penerima Kedua', 'penerima2@berbagibaik.test', 'receiver', '6281200000004');

        $jaket = $this->item($donor, 'Pakaian', 'Jaket Hoodie Abu-abu', 'Jaket hoodie ukuran L, bersih dan hangat.', 'Baik', 'Lhokseumawe', 'published', null, '#2F5597');
        $buku = $this->item($donor, 'Buku', 'Buku Pelajaran SMP', 'Satu set buku pelajaran kelas 7 sampai 9.', 'Seperti baru', 'Banda Aceh', 'published', null, '#548235');
        $mainan = $this->item($donor2, 'Mainan', 'Mainan Balok Edukasi', 'Balok susun kayu untuk anak usia 3 sampai 6 tahun.', 'Baik', 'Lhokseumawe', 'published', null, '#C55A11');
        $this->item($donor2, 'Perlengkapan Rumah Tangga', 'Rak Buku Kayu', 'Rak buku 4 tingkat, kayu masih kokoh.', 'Cukup baik', 'Lhokseumawe', 'published', null, '#7F6000');
        $this->item($donor, 'Perlengkapan Sekolah', 'Tas Sekolah Biru', 'Tas ransel sekolah, resleting berfungsi baik.', 'Baik', 'Banda Aceh', 'pending', null, '#1F4E79');
        $this->item($donor2, 'Elektronik', 'Kipas Angin Rusak', 'Kipas angin berdiri.', 'Cukup baik', 'Medan', 'rejected', 'Barang tidak berfungsi dan tidak layak donasi. Perbaiki dahulu lalu unggah kembali.', '#7B3F00');
        $selesai = $this->item($donor, 'Pakaian', 'Sepatu Anak', 'Sepatu anak ukuran 30, sedikit dipakai.', 'Baik', 'Lhokseumawe', 'completed', null, '#6B2D5C');

        // Pengajuan menunggu keputusan donatur
        $this->request($buku, $receiver, 'pending', 'Untuk adik saya yang duduk di kelas 8 dan membutuhkan buku pelajaran.');

        // Pengajuan sudah disetujui, menunggu serah terima (barang disembunyikan dari katalog)
        $this->request($mainan, $receiver2, 'awaiting_handover', 'Untuk anak-anak di taman baca lingkungan kami.', [
            'confirmation_code' => '123456',
            'code_expires_at' => now()->addDays(3),
        ]);

        // Donasi yang sudah selesai (muncul di riwayat dan membuat donatur terpercaya)
        $this->request($selesai, $receiver, 'completed', 'Sepatu anak saya sudah kekecilan.', [
            'confirmed_at' => now()->subDay(),
        ]);

        $this->notify($donor, 'Ada pengajuan baru untuk "Buku Pelajaran SMP" dari Penerima Demo.');
        $this->notify($donor, 'Barang "Jaket Hoodie Abu-abu" disetujui dan sudah dipublikasikan.');
        $this->notify($receiver2, 'Pengajuan Anda untuk "Mainan Balok Edukasi" disetujui donatur. Silakan atur serah terima barang.');
    }

    private function user(string $name, string $email, string $role, string $phone, int $verified = 0): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'demo12345', 'role' => $role, 'phone_wa' => $phone]
        );
        $user->forceFill(['verified_count' => $verified])->save();

        return $user;
    }

    private function item(User $donor, string $category, string $name, string $desc, string $condition, string $location, string $status, ?string $note, string $color): Item
    {
        return Item::firstOrCreate(
            ['donor_id' => $donor->id, 'name' => $name],
            [
                'category_id' => Category::where('name', $category)->value('id') ?? Category::value('id'),
                'description' => $desc,
                'condition' => $condition,
                'location' => $location,
                'photo_url' => $this->placeholder($name, $color),
                'status' => $status,
                'admin_note' => $note,
            ]
        );
    }

    private function request(Item $item, User $receiver, string $status, string $reason, array $extra = []): DonationRequest
    {
        return DonationRequest::firstOrCreate(
            ['item_id' => $item->id, 'receiver_id' => $receiver->id],
            array_merge(['reason' => $reason, 'status' => $status], $extra)
        );
    }

    private function notify(User $user, string $message): void
    {
        AppNotification::firstOrCreate(['user_id' => $user->id, 'message' => $message], ['is_read' => false]);
    }

    // Foto contoh berupa gambar SVG berwarna, tanpa perlu ekstensi GD
    private function placeholder(string $label, string $color): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">'
            . '<rect width="640" height="480" fill="' . $color . '"/>'
            . '<text x="320" y="250" font-family="Arial, sans-serif" font-size="36" fill="#ffffff" text-anchor="middle">'
            . htmlspecialchars($label, ENT_XML1) . '</text></svg>';

        $path = 'items/demo-' . Str::slug($label) . '.svg';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}

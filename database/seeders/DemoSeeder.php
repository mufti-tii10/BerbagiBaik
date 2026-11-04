<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Category;
use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\FundRequest;
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

        $this->funds($donor, $donor2, $receiver, $receiver2);

        $this->notify($donor, 'Ada pengajuan baru untuk "Buku Pelajaran SMP" dari Penerima Demo.');
        $this->notify($donor, 'Barang "Jaket Hoodie Abu-abu" disetujui dan sudah dipublikasikan.');
        $this->notify($receiver2, 'Pengajuan Anda untuk "Mainan Balok Edukasi" disetujui donatur. Silakan atur serah terima barang.');
    }

    // Data contoh donasi dana: satu tayang dengan donasi, satu tayang, satu menunggu verifikasi admin
    private function funds(User $donor, User $donor2, User $receiver, User $receiver2): void
    {
        $f1 = $this->fund($receiver, 'Biaya Pengobatan Ibu', 'Ibu saya membutuhkan biaya pengobatan rutin dan pembelian obat selama tiga bulan ke depan. Dana akan dipakai untuk obat dan transportasi ke rumah sakit.', 3000000, 30, 'BSI', 'Penerima Demo', '7100000001', 'published', '#2F5597');
        $this->donation($f1, $donor, 500000, 'confirmed', 'Semoga lekas sembuh.', false);
        $this->donation($f1, $donor2, 250000, 'confirmed', null, true);
        $this->donation($f1, $donor, 100000, 'pending', null, false);

        $f2 = $this->fund($receiver2, 'Buku untuk Taman Baca', 'Kami mengelola taman baca kecil untuk anak-anak sekitar dan membutuhkan dana untuk membeli buku bacaan dan rak sederhana.', 1000000, 20, 'Dana', 'Penerima Kedua', '081200000004', 'published', '#548235');
        $this->donation($f2, $donor, 300000, 'confirmed', 'Untuk adik-adik pembaca.', false);

        $this->fund($receiver2, 'Perbaikan Atap Mushalla Desa', 'Atap mushalla desa bocor dan perlu diperbaiki sebelum musim hujan. Dana digunakan untuk membeli seng dan upah tukang.', 5000000, 45, 'BRI', 'Penerima Kedua', '3300000004', 'pending', '#C55A11');
    }

    private function fund(User $receiver, string $title, string $desc, int $target, int $days, string $bank, string $accName, string $accNo, string $status, string $color): FundRequest
    {
        return FundRequest::firstOrCreate(
            ['receiver_id' => $receiver->id, 'title' => $title],
            [
                'description' => $desc,
                'target_amount' => $target,
                'deadline' => now()->addDays($days)->toDateString(),
                'bank_name' => $bank,
                'account_name' => $accName,
                'account_number' => $accNo,
                'photo_url' => $this->placeholder($title, $color),
                'proof_doc_url' => $this->privatePlaceholder('Dokumen ' . $title),
                'status' => $status,
            ]
        );
    }

    private function donation(FundRequest $fund, User $donor, int $amount, string $status, ?string $message, bool $anonymous): Donation
    {
        return Donation::firstOrCreate(
            ['fund_request_id' => $fund->id, 'donor_id' => $donor->id, 'amount' => $amount],
            [
                'proof_photo_url' => $this->privatePlaceholder('Bukti transfer ' . $fund->id . '-' . $donor->id . '-' . $amount),
                'message' => $message,
                'is_anonymous' => $anonymous,
                'status' => $status,
                'confirmed_at' => $status === 'confirmed' ? now()->subDays(2) : null,
            ]
        );
    }

    // Dokumen dan bukti contoh disimpan privat (bukan di folder publik)
    private function privatePlaceholder(string $label): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">'
            . '<rect width="640" height="480" fill="#f3f4f6"/>'
            . '<text x="320" y="250" font-family="Arial, sans-serif" font-size="28" fill="#374151" text-anchor="middle">'
            . htmlspecialchars($label, ENT_XML1) . '</text></svg>';

        $path = 'fund-docs/demo-' . Str::slug($label) . '.svg';
        Storage::disk('local')->put($path, $svg);

        return $path;
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

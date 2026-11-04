<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function storeFund(Request $request, FundRequest $fund)
    {
        $user = $request->user();

        abort_if($user->isAdmin() || $fund->receiver_id === $user->id, 403);
        abort_unless(in_array($fund->status, ['published', 'completed', 'closed'], true), 404);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan laporan wajib diisi.',
            'reason.min' => 'Alasan terlalu singkat, tulis minimal 10 karakter.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $already = Report::where('reporter_id', $user->id)->where('fund_request_id', $fund->id)->where('status', 'open')->exists();
        if ($already) {
            return back()->with('error', 'Anda sudah melaporkan kebutuhan ini. Laporan sedang ditinjau admin.');
        }

        Report::create([
            'reporter_id' => $user->id,
            'fund_request_id' => $fund->id,
            'reason' => $data['reason'],
            'status' => 'open',
        ]);

        return back()->with('success', 'Laporan terkirim. Admin akan meninjaunya.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaCallback;
use App\Helpers\SystemHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MpesaCallbackController extends Controller
{
    public function index(Request $request)
    {
        $callbacks = $this->filteredQuery($request)->paginate(25)->withQueryString();

        return view('admin.mpesa-callbacks.index', [
            'callbacks' => $callbacks,
            'counts'    => $this->counts(),
        ]);
    }

    public function show(MpesaCallback $callback)
    {
        $callback->load(['mpesaStk', 'order']);
        return view('admin.mpesa-callbacks.show', compact('callback'));
    }

    /**
     * Export the current filtered list as a PDF.
     */
    public function exportPdf(Request $request)
    {
        $callbacks = $this->filteredQuery($request)->get();

        // Embed logo as base64 so dompdf can render it reliably
        $logoData = null;
        $logoPath = public_path('images/logo/logo.png');
        if (is_file($logoPath)) {
            $mime = mime_content_type($logoPath) ?: 'image/png';
            $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('admin.mpesa-callbacks.pdf', [
            'callbacks'    => $callbacks,
            'counts'       => $this->counts(),
            'appName'      => SystemHelper::appName() ?? config('app.name'),
            'primaryColor' => SystemHelper::primaryColor() ?? '#465FFF',
            'logoData'     => $logoData,
            'generatedAt'  => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'mpesa-callbacks-' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /* ---------------------------------------------------------
     |  Helpers
     |--------------------------------------------------------- */

    protected function filteredQuery(Request $request)
    {
        $query = MpesaCallback::with(['mpesaStk', 'order'])->latest();

        if ($status = $request->input('status')) {
            $query->where('processing_status', $status);
        }

        if ($phone = $request->input('phone')) {
            $query->where('phone_number', 'like', "%{$phone}%");
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('mpesa_receipt_number', 'like', "%{$search}%")
                  ->orWhere('checkout_request_id', 'like', "%{$search}%")
                  ->orWhere('merchant_request_id', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    protected function counts(): array
    {
        return [
            'all'       => MpesaCallback::count(),
            'received'  => MpesaCallback::where('processing_status', 'received')->count(),
            'processed' => MpesaCallback::where('processing_status', 'processed')->count(),
            'failed'    => MpesaCallback::where('processing_status', 'failed')->count(),
        ];
    }
}
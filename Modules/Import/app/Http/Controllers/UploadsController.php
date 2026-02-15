<?php

namespace Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Import\Jobs\ProcessBankStatement;
use Modules\Import\Jobs\ProcessGpayScreenshot;
use Modules\Import\Models\Upload;

class UploadsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Import/Uploads/Index', [
            'uploads' => Upload::with('uploader')
                ->latest()
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'file_path' => $u->file_path,
                    'type' => $u->type,
                    'status' => $u->status,
                    'processed_at' => $u->processed_at?->format('d-m-Y H:i'),
                    'uploaded_by' => $u->uploader?->name,
                    'created_at' => $u->created_at->format('d-m-Y H:i'),
                    'transactions_count' => $u->parsedTransactions()->count(),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Import/Uploads/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'type' => ['required', 'in:gpay_screenshot,bank_statement'],
        ]);

        $path = $request->file('file')->store('uploads');

        $upload = Upload::create([
            'file_path' => $path,
            'type' => $validated['type'],
            'status' => 'pending',
            'uploaded_by' => $request->user()->id,
        ]);

        if ($upload->type === 'gpay_screenshot') {
            ProcessGpayScreenshot::dispatch($upload);
        } else {
            ProcessBankStatement::dispatch($upload);
        }

        return redirect()->route('uploads.index')
            ->with('success', 'File uploaded. Processing will begin shortly.');
    }

    public function destroy(Upload $upload): RedirectResponse
    {
        $upload->delete();

        return redirect()->route('uploads.index')->with('success', 'Upload deleted.');
    }
}

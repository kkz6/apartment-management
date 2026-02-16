<?php

namespace Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                ->withCount('parsedTransactions as transactions_count')
                ->latest()
                ->paginate(15)
                ->through(fn ($u) => [
                    'id' => $u->id,
                    'file_path' => $u->file_path,
                    'type' => $u->type,
                    'status' => $u->status,
                    'processed_at' => $u->processed_at?->format('d-m-Y H:i'),
                    'uploaded_by' => $u->uploader?->name,
                    'created_at' => $u->created_at->format('d-m-Y H:i'),
                    'transactions_count' => $u->transactions_count,
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
            'password' => ['nullable', 'string', 'max:255'],
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
            $password = $validated['password'] ?? null;
            ProcessBankStatement::dispatch($upload, $password ?: null);
        }

        return redirect()->route('uploads.index')
            ->with('success', 'File uploaded. Processing will begin shortly.');
    }

    public function retry(Request $request, Upload $upload): RedirectResponse
    {
        $request->validate([
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        if (! Storage::exists($upload->file_path)) {
            return redirect()->route('uploads.index')
                ->with('error', 'Source file no longer exists. Please re-upload.');
        }

        $upload->parsedTransactions()->delete();

        $upload->update([
            'status' => 'pending',
            'processed_at' => null,
        ]);

        if ($upload->type === 'gpay_screenshot') {
            ProcessGpayScreenshot::dispatch($upload);
        } else {
            $password = $request->input('password');
            ProcessBankStatement::dispatch($upload, $password ?: null);
        }

        return redirect()->route('uploads.index')
            ->with('success', 'Upload queued for reprocessing.');
    }

    public function destroy(Upload $upload): RedirectResponse
    {
        $upload->delete();

        return redirect()->route('uploads.index')->with('success', 'Upload deleted.');
    }
}

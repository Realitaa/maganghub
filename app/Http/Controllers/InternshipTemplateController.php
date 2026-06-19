<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InternshipTemplateController extends Controller
{
    /**
     * Display the template management page.
     */
    public function index(Request $request): InertiaResponse
    {
        if (! in_array($request->user()->role, ['operator', 'administrator'])) {
            abort(403, 'Unauthorized.');
        }

        $path = 'templates/letter_template.docx';
        $exists = Storage::exists($path);
        $size = $exists ? $this->formatBytes(Storage::size($path)) : null;
        $updatedAt = $exists ? Carbon::createFromTimestamp(Storage::lastModified($path))->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : null;

        return Inertia::render('operator/templates/Index', [
            'template' => compact('exists', 'size', 'updatedAt'),
        ]);
    }

    /**
     * Store/update the uploaded template file.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! in_array($request->user()->role, ['operator', 'administrator'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'file' => 'required|file|max:5120',
        ], [
            'file.required' => 'File template wajib diunggah.',
            'file.file' => 'Berkas harus berupa file.',
            'file.max' => 'Ukuran file template tidak boleh lebih dari 5 MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'docx') {
            return redirect()->back()->withErrors([
                'file' => 'Format file template harus berupa berkas Word (.docx).',
            ]);
        }

        $dir = 'templates';
        if (! Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        Storage::putFileAs($dir, $file, 'letter_template.docx');

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Template surat permohonan magang berhasil diperbarui.',
        ])->back();
    }

    /**
     * Format file size helper.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}

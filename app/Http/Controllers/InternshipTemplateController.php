<?php

namespace App\Http\Controllers;

use App\Http\Requests\Templates\StoreTemplateRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('manage-templates');

        $path = 'templates/letter_template.docx';
        $exists = Storage::exists($path);
        $size = $exists ? $this->formatBytes(Storage::size($path)) : null;
        $updatedAt = $exists ? Carbon::createFromTimestamp(Storage::lastModified($path))->timezone('Asia/Jakarta') : null;

        return Inertia::render('review/templates/Index', [
            'template' => compact('exists', 'size', 'updatedAt'),
        ]);
    }

    /**
     * Store/update the uploaded template file.
     */
    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        Gate::authorize('manage-templates');

        $file = $request->file('file');

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

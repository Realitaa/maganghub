<?php

namespace App\Http\Controllers;

use App\Http\Requests\Templates\StoreTemplateRequest;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Services\DocumentGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

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

        // Fetch latest 5 unique groups that have submitted an internship
        $recentGroups = InternshipSubmission::query()
            ->with(['group.leader'])
            ->latest()
            ->get()
            ->filter(fn ($submission) => $submission->group !== null)
            ->unique('group_id')
            ->take(5)
            ->map(function (InternshipSubmission $submission) {
                return [
                    'id' => $submission->group->id,
                    'code' => $submission->group->code,
                    'leader_name' => $submission->group->leader?->name ?? 'Tanpa Ketua',
                    'company_name' => $submission->company_name ?? 'Belum Ditentukan',
                    'submission_id' => $submission->id,
                    'label' => "Kelompok {$submission->group->code} — {$submission->company_name} ({$submission->group->leader?->name})",
                ];
            })
            ->values()
            ->all();

        return Inertia::render('internships/templates/Index', [
            'template' => compact('exists', 'size', 'updatedAt'),
            'recentGroups' => $recentGroups,
            'placeholders' => DocumentGeneratorService::getAvailablePlaceholders(),
        ]);
    }

    /**
     * Download or stream the raw letter template file for browser preview.
     */
    public function rawTemplate(): Response
    {
        Gate::authorize('manage-templates');

        $path = 'templates/letter_template.docx';

        if (! Storage::exists($path)) {
            abort(404, 'Template surat belum diunggah.');
        }

        return response(Storage::get($path), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="letter_template.docx"',
        ]);
    }

    /**
     * Process and generate preview document overwritten with submission data.
     */
    public function processPreview(Request $request, DocumentGeneratorService $generatorService): Response
    {
        Gate::authorize('manage-templates');

        $groupId = $request->input('group_id') ?? $request->query('group_id');

        if (! $groupId || ! is_numeric($groupId)) {
            abort(422, 'Parameter group_id wajib diisi.');
        }

        $group = InternshipGroup::findOrFail((int) $groupId);
        $submission = $group->activeSubmission ?? $group->submissions()->latest()->first();

        if (! $submission) {
            abort(422, 'Kelompok ini belum memiliki berkas pengajuan magang.');
        }

        try {
            $processedPath = $generatorService->generatePreview($submission);
        } catch (\Throwable $e) {
            abort(422, 'Gagal memproses template: '.$e->getMessage());
        }

        return response(Storage::get($processedPath), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="letter_template_processed.docx"',
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

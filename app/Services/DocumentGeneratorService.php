<?php

namespace App\Services;

use App\Models\InternshipSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class DocumentGeneratorService
{
    /**
     * Generate the internship application letter from the local template.
     *
     * @throws RuntimeException
     */
    public function generateLetter(InternshipSubmission $submission, ?int $userId = null): string
    {
        $templatePath = 'templates/letter_template.docx';

        if (! Storage::exists($templatePath)) {
            throw new RuntimeException('Template surat permohonan magang belum diunggah oleh administrator.');
        }

        $templateContent = Storage::get($templatePath);

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        if (file_put_contents($tempFile, $templateContent) === false) {
            throw new RuntimeException('Gagal menulis berkas template sementara.');
        }

        // Process DOCX ZIP archive
        $zip = new ZipArchive;
        if ($zip->open($tempFile) !== true) {
            unlink($tempFile);
            throw new RuntimeException('Gagal membuka berkas DOCX.');
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        if ($xmlContent === false) {
            $zip->close();
            unlink($tempFile);
            throw new RuntimeException('Konten word/document.xml tidak ditemukan.');
        }

        // Extract body content and section properties
        if (! preg_match('/<w:body>(.*)(<w:sectPr[^>]*>.*<\/w:sectPr>)\s*<\/w:body>/s', $xmlContent, $matches)) {
            $zip->close();
            unlink($tempFile);
            throw new RuntimeException('Gagal memproses struktur w:body berkas template.');
        }

        $repeatingContent = $matches[1];
        $sectPr = $matches[2];

        // Prepare placeholder data
        $number = '00'.$submission->id.'/UN33.4/KM/'.now()->format('Y');
        $today = $this->formatIndonesianDate(now());
        $companyName = $submission->company_name ?? '';
        $monthDuration = $this->calculateMonthDuration($submission->start_date, $submission->end_date);
        $startDate = $this->formatIndonesianDate($submission->start_date);
        $endDate = $this->formatIndonesianDate($submission->end_date);
        $fieldOfInterest = $submission->field_of_interest ?? '';
        $division = $submission->division ?? '';
        $divisionOrInterest = ! empty($division) ? $division : $fieldOfInterest;

        $query = $submission->submissionMemberships()->with('user');
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        $memberships = $query->get();

        if ($memberships->isEmpty()) {
            $zip->close();
            unlink($tempFile);
            throw new RuntimeException('Tidak ada anggota kelompok yang ditemukan untuk pengajuan ini.');
        }

        $pages = [];

        foreach ($memberships as $membership) {
            $user = $membership->user;
            if (! $user) {
                continue;
            }

            $pageXml = $repeatingContent;

            // Replace student placeholders
            $pageXml = str_replace('{{name}}', htmlspecialchars($user->name ?? ''), $pageXml);
            $pageXml = str_replace('{{nim}}', htmlspecialchars($user->nim ?? ''), $pageXml);
            $pageXml = str_replace('{{semester}}', htmlspecialchars((string) ($user->semester ?? '')), $pageXml);
            $pageXml = str_replace('{{phone}}', htmlspecialchars($user->phone ?? ''), $pageXml);
            $pageXml = str_replace('{{email}}', htmlspecialchars($user->email ?? ''), $pageXml);

            // Replace submission placeholders
            $pageXml = str_replace('{{number}}', htmlspecialchars($number), $pageXml);
            $pageXml = str_replace('{{today}}', htmlspecialchars($today), $pageXml);
            $pageXml = str_replace('{{company_name}}', htmlspecialchars($companyName), $pageXml);
            $pageXml = str_replace('{{start_date}}', htmlspecialchars($startDate), $pageXml);
            $pageXml = str_replace('{{end_date}}', htmlspecialchars($endDate), $pageXml);
            $pageXml = str_replace('{{calculateDuration}}', htmlspecialchars($monthDuration), $pageXml);
            $pageXml = str_replace('{{field_of_interest}}', htmlspecialchars($fieldOfInterest), $pageXml);
            $pageXml = str_replace('{{division}}', htmlspecialchars($divisionOrInterest), $pageXml);

            $pages[] = $pageXml;
        }

        // Join each page using an OpenXML page break
        $pageBreak = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
        $newBodyXml = '<w:body>'.implode($pageBreak, $pages).$sectPr.'</w:body>';

        // Reconstruct the XML content
        $xmlContent = preg_replace('/<w:body>.*<\/w:body>/s', $newBodyXml, $xmlContent);

        // Save XML back to the ZIP
        $zip->addFromString('word/document.xml', $xmlContent);
        $zip->close();

        // Save to private storage
        $storageDir = 'letters';
        if (! Storage::exists($storageDir)) {
            Storage::makeDirectory($storageDir);
        }

        $storagePath = $storageDir.'/permohonan_magang_'.$submission->id.'_'.uniqid().'.docx';
        Storage::put($storagePath, file_get_contents($tempFile));

        // Cleanup
        unlink($tempFile);

        return $storagePath;
    }

    /**
     * Format a date into Indonesian format.
     */
    private function formatIndonesianDate($date): string
    {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM YYYY');
    }

    /**
     * Calculate month duration.
     */
    private function calculateMonthDuration($startDate, $endDate): string
    {
        if (! $startDate || ! $endDate) {
            return '-';
        }
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $diff = $start->diff($end);
        $months = ($diff->y * 12) + $diff->m;
        if ($diff->d > 15) {
            $months += 1;
        }
        $months = max(1, $months);

        return $months.' Bulan';
    }
}

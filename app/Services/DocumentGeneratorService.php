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
    /**
     * Generate the internship application letter from the local template.
     *
     * @throws RuntimeException
     */
    public function generateLetter(InternshipSubmission $submission): string
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

        // Fetch memberships
        $memberships = $submission->submissionMemberships()->with('user')->get();

        if ($memberships->isEmpty()) {
            $zip->close();
            unlink($tempFile);
            throw new RuntimeException('Tidak ada anggota kelompok yang ditemukan untuk pengajuan ini.');
        }

        // 1. Process table rows for students
        $tblStart = strpos($xmlContent, '<w:tbl>');
        if ($tblStart !== false) {
            $headerStart = strpos($xmlContent, '<w:tr>', $tblStart);
            $tblEnd = strpos($xmlContent, '</w:tbl>', $tblStart);

            if ($headerStart !== false && $tblEnd !== false && $headerStart < $tblEnd) {
                $headerEnd = strpos($xmlContent, '</w:tr>', $headerStart);
                if ($headerEnd !== false && $headerEnd < $tblEnd) {
                    $headerEnd += strlen('</w:tr>');

                    // Generate row XML for each member
                    $rowsXml = '';
                    $index = 1;
                    foreach ($memberships as $membership) {
                        $user = $membership->user;
                        if (!$user) {
                            continue;
                        }
                        $rowsXml .= $this->generateStudentRow($index, $user->name, $user->nim);
                        $index++;
                    }

                    // Assemble the new XML table
                    $prefix = substr($xmlContent, 0, $headerEnd);
                    $suffix = substr($xmlContent, $tblEnd);
                    $xmlContent = $prefix . $rowsXml . $suffix;
                }
            }
        }

        // 2. Replace body placeholders
        $today = $this->formatIndonesianDate(now());
        $companyName = $submission->company_name ?? '';
        $startDate = $this->formatIndonesianDate($submission->start_date);
        $endDate = $this->formatIndonesianDate($submission->end_date);

        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{{company_name}}', $companyName);
        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{{start_date}}', $startDate);
        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{start_date}}', $startDate);
        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{{end_date}}', $endDate);
        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{end_date}}', $endDate);
        $xmlContent = $this->replaceWordPlaceholder($xmlContent, '{{today}}', $today);

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
     * Generate table row XML for a single student.
     */
    private function generateStudentRow(int $index, string $name, string $nim, string $programStudi = 'Ilmu Komputer'): string
    {
        $escapedIndex = htmlspecialchars((string)$index);
        $escapedName = htmlspecialchars($name);
        $escapedNim = htmlspecialchars($nim);
        $escapedProdi = htmlspecialchars($programStudi);

        return '<w:tr><w:trPr></w:trPr>' .
            '<w:tc><w:tcPr><w:tcW w:w="788" w:type="dxa"/><w:tcBorders></w:tcBorders></w:tcPr><w:p><w:pPr><w:pStyle w:val="Normal"/><w:widowControl/><w:spacing w:lineRule="auto" w:line="240" w:before="0" w:after="140"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs=""/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:cs="" w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr><w:t>' . $escapedIndex . '</w:t></w:r></w:p></w:tc>' .
            '<w:tc><w:tcPr><w:tcW w:w="3800" w:type="dxa"/><w:tcBorders></w:tcBorders></w:tcPr><w:p><w:pPr><w:pStyle w:val="Normal"/><w:widowControl/><w:spacing w:lineRule="auto" w:line="240" w:before="0" w:after="140"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs=""/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:cs="" w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr><w:t>' . $escapedName . '</w:t></w:r></w:p></w:tc>' .
            '<w:tc><w:tcPr><w:tcW w:w="2300" w:type="dxa"/><w:tcBorders></w:tcBorders></w:tcPr><w:p><w:pPr><w:pStyle w:val="Normal"/><w:widowControl/><w:spacing w:lineRule="auto" w:line="240" w:before="0" w:after="140"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs=""/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:cs="" w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr><w:t>' . $escapedNim . '</w:t></w:r></w:p></w:tc>' .
            '<w:tc><w:tcPr><w:tcW w:w="2725" w:type="dxa"/><w:tcBorders></w:tcBorders></w:tcPr><w:p><w:pPr><w:pStyle w:val="Normal"/><w:widowControl/><w:spacing w:lineRule="auto" w:line="240" w:before="0" w:after="140"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs=""/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:cs="" w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:kern w:val="0"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr><w:t>' . $escapedProdi . '</w:t></w:r></w:p></w:tc>' .
            '</w:tr>';
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

    /**
     * Replaces a placeholder in word document XML, robustly handling XML tag splits inside the placeholder.
     */
    private function replaceWordPlaceholder(string $xml, string $placeholder, string $replacement): string
    {
        $chars = str_split($placeholder);
        $pattern = '';
        foreach ($chars as $char) {
            $quoted = preg_quote($char, '/');
            if ($pattern === '') {
                $pattern = $quoted;
            } else {
                $pattern .= '(?:<[^>]+>)*' . $quoted;
            }
        }
        return preg_replace('/' . $pattern . '/s', htmlspecialchars($replacement), $xml);
    }
}

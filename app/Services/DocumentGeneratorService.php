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

        // Prepare placeholder data
        $number = '00'.$submission->id.'/UN33.4/KM/'.now()->format('Y');
        $today = $this->formatIndonesianDate(now());
        $dear = 'Pimpinan '.($submission->company_name ?? '');
        $place = $submission->company_name ?? '';
        $monthDuration = $this->calculateMonthDuration($submission->start_date, $submission->end_date);
        $startDate = $this->formatIndonesianDate($submission->start_date);
        $endDate = $this->formatIndonesianDate($submission->end_date);
        $typedName = $submission->group->leader->name ?? '';
        $nim = $submission->group->leader->nim ?? '';

        // Generate snapshot members table XML
        $memberships = $submission->submissionMemberships()->with('user')->get();
        $internTableXml = $this->generateInternTableXml($memberships);

        // Replace basic placeholders
        $xmlContent = str_replace('{{number}}', htmlspecialchars($number), $xmlContent);
        $xmlContent = str_replace('{{today}}', htmlspecialchars($today), $xmlContent);
        $xmlContent = str_replace('{{dear}}', htmlspecialchars($dear), $xmlContent);
        $xmlContent = str_replace('{{place}}', htmlspecialchars($place), $xmlContent);
        $xmlContent = str_replace('{{monthDuration}}', htmlspecialchars($monthDuration), $xmlContent);
        $xmlContent = str_replace('{{startDate}}', htmlspecialchars($startDate), $xmlContent);
        $xmlContent = str_replace('{{endDate}}', htmlspecialchars($endDate), $xmlContent);
        $xmlContent = str_replace('{{typedName}}', htmlspecialchars($typedName), $xmlContent);
        $xmlContent = str_replace('{{nim}}', htmlspecialchars($nim), $xmlContent);

        // Replace table placeholder by splitting the paragraph run tags
        $tableReplacement = '</w:t></w:r></w:p>'.$internTableXml.'<w:p><w:r><w:t xml:space="preserve">';
        $xmlContent = str_replace('{{internTable}}', $tableReplacement, $xmlContent);

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
     * Generate OpenXML table for memberships snapshot.
     */
    private function generateInternTableXml($memberships): string
    {
        $xml = '<w:tbl>';

        // Table properties
        $xml .= '<w:tblPr>';
        $xml .= '<w:tblW w:w="5000" w:type="pct"/>';
        $xml .= '<w:tblBorders>';
        $xml .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
        $xml .= '</w:tblBorders>';
        $xml .= '</w:tblPr>';

        // Grid columns
        $xml .= '<w:tblGrid>';
        $xml .= '<w:gridCol w:w="800"/>';
        $xml .= '<w:gridCol w:w="4200"/>';
        $xml .= '<w:gridCol w:w="2500"/>';
        $xml .= '</w:tblGrid>';

        // Header Row
        $xml .= '<w:tr>';
        $xml .= '<w:tc><w:tcPr><w:tcW w:w="800" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>No</w:t></w:r></w:p></w:tc>';
        $xml .= '<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Nama</w:t></w:r></w:p></w:tc>';
        $xml .= '<w:tc><w:tcPr><w:tcW w:w="2500" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>NIM</w:t></w:r></w:p></w:tc>';
        $xml .= '</w:tr>';

        // Data Rows
        $index = 1;
        foreach ($memberships as $membership) {
            $name = htmlspecialchars($membership->user->name ?? '');
            $nim = htmlspecialchars($membership->user->nim ?? '');

            $xml .= '<w:tr>';
            $xml .= '<w:tc><w:tcPr><w:tcW w:w="800" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:t>'.$index.'</w:t></w:r></w:p></w:tc>';
            $xml .= '<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr><w:p><w:r><w:t>'.$name.'</w:t></w:r></w:p></w:tc>';
            $xml .= '<w:tc><w:tcPr><w:tcW w:w="2500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:t>'.$nim.'</w:t></w:r></w:p></w:tc>';
            $xml .= '</w:tr>';
            $index++;
        }

        $xml .= '</w:tbl>';

        return $xml;
    }

    /**
     * Format a date into Indonesian format.
     */
    private function formatIndonesianDate($date): string
    {
        if (! $date) {
            return '-';
        }
        $carbon = Carbon::parse($date);
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $carbon->day.' '.$months[$carbon->month].' '.$carbon->year;
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

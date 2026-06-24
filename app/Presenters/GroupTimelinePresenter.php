<?php

namespace App\Presenters;

use App\Enums\GroupTimelineType;
use App\Models\GroupTimeline;

class GroupTimelinePresenter
{
    /**
     * Get the formatted title of a timeline record.
     */
    public static function title(GroupTimeline $timeline): string
    {
        switch ($timeline->type) {
            case GroupTimelineType::SubmissionCreated:
                return 'Pengajuan magang berhasil dan akan diperiksa oleh operator/administrator. Tunggu hasilnya ya.';

            case GroupTimelineType::SubmissionRejected:
                $message = 'Pengajuan magang kelompokmu ditolak oleh operator/administrator.';
                $reason = $timeline->metadata['reason'] ?? null;
                if (! empty($reason)) {
                    $message .= "\n\nAlasan:\n{$reason}";
                }

                return $message;

            case GroupTimelineType::SubmissionApproved:
                return 'Pengajuan magang kelompokmu diterima oleh operator/administrator. Silakan berkoordinasi dengan mereka.';

            case GroupTimelineType::ApplicationLetterPrinted:
                return 'Surat pengajuan magangmu sudah dicetak. Silakan dibawa ke perusahaan tujuan dan semoga beruntung ya.';

            case GroupTimelineType::CompanyReplyUploaded:
                return 'Kamu sudah mengupload surat balasan perusahaan. Tunggu review dari operator/administrator dan silakan berkoordinasi jika diperlukan.';

            case GroupTimelineType::AdministrationCompleted:
                return 'Administrasi magang kelompokmu sudah selesai. Semangat menjalani magangnya.';

            default:
                return '';
        }
    }
}

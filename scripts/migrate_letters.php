<?php

use App\Models\InternshipSubmission;
use App\Services\DocumentGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting migration of internship letters...\n";

// 1. Fetch all submissions that are already approved/published
$submissions = DB::table('internship_submissions')
    ->whereIn('status', ['letter_published', 'applying', 'loa_review', 'accepted', 'partially_accepted'])
    ->get();

$count = 0;
foreach ($submissions as $sub) {
    echo "Processing submission ID: {$sub->id} for company: {$sub->company_name}\n";

    // 2. Fetch and delete old individual letters from storage
    $memberships = DB::table('submission_memberships')
        ->where('submission_id', $sub->id)
        ->get();

    foreach ($memberships as $membership) {
        if (!empty($membership->letter_path)) {
            if (Storage::exists($membership->letter_path)) {
                Storage::delete($membership->letter_path);
                echo "  Deleted old individual letter: {$membership->letter_path}\n";
            }
        }
    }

    // 3. Generate the consolidated group letter
    if (Storage::exists('templates/letter_template.docx')) {
        try {
            $submissionModel = InternshipSubmission::find($sub->id);
            if ($submissionModel) {
                $generator = app(DocumentGeneratorService::class);
                $newPath = $generator->generateLetter($submissionModel);
                
                DB::table('internship_submissions')
                    ->where('id', $sub->id)
                    ->update(['letter_path' => $newPath]);

                echo "  Generated new consolidated letter: {$newPath}\n";
                $count++;
            }
        } catch (\Exception $e) {
            echo "  Error generating letter: {$e->getMessage()}\n";
            Log::error("Failed to generate consolidated letter for submission ID {$sub->id}: " . $e->getMessage());
        }
    } else {
        echo "  Warning: Template templates/letter_template.docx not found. Skipping generation.\n";
    }
}

echo "Migration completed! Regenerated {$count} consolidated letters.\n";

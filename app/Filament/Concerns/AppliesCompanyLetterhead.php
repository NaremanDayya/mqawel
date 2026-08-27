<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

/**
 * Applies a company's letterhead (companies.letterhead, on the public disk)
 * as the background of every page of a generated PDF — a PDF letterhead is
 * imported as a repeating document template, an image letterhead is set as
 * a full-page watermark image.
 */
trait AppliesCompanyLetterhead
{
    protected static function applyLetterhead(Mpdf $mpdf, ?string $letterheadPath): void
    {
        if (blank($letterheadPath)) {
            return;
        }

        $absolutePath = Storage::disk('public')->path($letterheadPath);

        if (! is_file($absolutePath)) {
            return;
        }

        if (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) === 'pdf') {
            $mpdf->SetDocTemplate($absolutePath, true);

            return;
        }

        $mpdf->SetWatermarkImage($absolutePath, 1, 'F', 'F');
        $mpdf->showWatermarkImage = true;
    }
}

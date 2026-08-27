<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Throwable;

/**
 * Applies a company's letterhead (companies.letterhead, on the public disk)
 * as the background of every page of a generated PDF — a PDF letterhead is
 * imported as a repeating document template, an image letterhead is set as
 * a full-page watermark image.
 */
trait AppliesCompanyLetterhead
{
    /**
     * A reusable, optional "letterhead" upload field — pre-filled with the
     * company's currently saved letterhead so it only needs uploading once
     * and shows up already-set everywhere else this field is used.
     */
    protected static function letterheadFormField(): FileUpload
    {
        return FileUpload::make('letterhead')
            ->label(__('backend.letterhead'))
            ->helperText(__('backend.letterhead_hint'))
            ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg', 'image/webp'])
            ->directory('letterheads')
            ->maxSize(10240)
            ->openable()
            ->default(fn () => Auth::user()->company->letterhead)
            ->columnSpanFull();
    }

    /**
     * Pulls the 'letterhead' key out of a submitted form's $data (so it
     * doesn't leak into a model's create/update payload), saves it onto the
     * current user's company, and returns the resolved letterhead path.
     */
    protected static function persistLetterhead(array &$data): ?string
    {
        $company = Auth::user()->company;

        if (! array_key_exists('letterhead', $data)) {
            return $company->letterhead;
        }

        $letterhead = $data['letterhead'];
        unset($data['letterhead']);

        $company->update(['letterhead' => $letterhead]);

        return $letterhead;
    }

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

    /**
     * Stamps a company's letterhead behind an already-uploaded PDF file (an
     * uploaded document, not something generated from markdown content) —
     * imports every page of the uploaded PDF and redraws it on top of a page
     * that already has the letterhead as its background. Only PDFs are
     * supported; any other file type (image, docx, …) is returned untouched,
     * since compositing a letterhead behind an arbitrary image doesn't make
     * the same sense.
     *
     * @return string The relative (public disk) path to use instead of $relativeFilePath — unchanged if stamping wasn't applicable or failed.
     */
    protected static function stampLetterheadOnUploadedFile(?string $relativeFilePath, ?string $letterheadPath): ?string
    {
        if (blank($relativeFilePath) || blank($letterheadPath)) {
            return $relativeFilePath;
        }

        if (strtolower(pathinfo($relativeFilePath, PATHINFO_EXTENSION)) !== 'pdf') {
            return $relativeFilePath;
        }

        $letterheadAbsolute = Storage::disk('public')->path($letterheadPath);

        if (! is_file($letterheadAbsolute) || strtolower(pathinfo($letterheadAbsolute, PATHINFO_EXTENSION)) !== 'pdf') {
            return $relativeFilePath;
        }

        $sourceAbsolute = Storage::disk('public')->path($relativeFilePath);

        if (! is_file($sourceAbsolute)) {
            return $relativeFilePath;
        }

        try {
            $mpdf = new Mpdf(['tempDir' => storage_path('app/mpdf/tmp')]);
            static::applyLetterhead($mpdf, $letterheadPath);

            $pageCount = $mpdf->setSourceFile($sourceAbsolute);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $mpdf->importPage($page);
                $size = $mpdf->getTemplateSize($templateId);

                $mpdf->AddPageByArray([
                    'orientation' => $size['width'] > $size['height'] ? 'L' : 'P',
                ]);

                $mpdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
            }

            $stampedRelativePath = pathinfo($relativeFilePath, PATHINFO_DIRNAME).'/'
                .pathinfo($relativeFilePath, PATHINFO_FILENAME).'-letterhead.pdf';

            Storage::disk('public')->put($stampedRelativePath, $mpdf->Output('', Destination::STRING_RETURN));

            return $stampedRelativePath;
        } catch (Throwable $e) {
            report($e);

            return $relativeFilePath;
        }
    }
}

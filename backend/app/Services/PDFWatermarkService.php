<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class PDFWatermarkService
{
    /**
     * Add watermark and QR code to PDF and return temporary file path
     */
    public function addWatermark(string $pdfPath, string $text = 'BELUM APPROVE', ?string $qrCodePath = null, string $qrPosition = 'top-right'): string
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($pdfPath)) {
            throw new \Exception('PDF file not found');
        }

        // Get full path
        $fullPath = Storage::disk('public')->path($pdfPath);

        // Create new PDF instance
        $pdf = new Fpdi();

        // Get total pages
        $pageCount = $pdf->setSourceFile($fullPath);

        // Process each page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import page
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // Add page with same size
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Add watermark and QR code
            $this->addWatermarkToPage($pdf, $text, $size['width'], $size['height'], $qrCodePath, $qrPosition);
        }

        // Generate temporary file
        $tempFilename = 'temp_' . uniqid() . '.pdf';
        $tempPath = Storage::disk('public')->path('temp/' . $tempFilename);

        // Ensure temp directory exists
        $tempDir = dirname($tempPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Save watermarked PDF
        $pdf->Output($tempPath, 'F');

        return 'temp/' . $tempFilename;
    }

    /**
     * Add watermark text and QR code to a page
     * Note: Using light gray color instead of alpha transparency for FPDI compatibility
     * No rotation support in FPDI, using centered horizontal text instead
     */
    private function addWatermarkToPage(Fpdi $pdf, string $text, float $pageWidth, float $pageHeight, ?string $qrCodePath = null, string $qrPosition = 'top-right'): void
    {
        // Add watermark text
        $pdf->SetFont('Arial', 'B', 50);
        $pdf->SetTextColor(200, 200, 200); // Light gray color for watermark effect

        // Calculate text width and height
        $textWidth = $pdf->GetStringWidth($text);
        $textHeight = 50; // Approximate height for 50pt font

        // Center the text horizontally and vertically
        $x = ($pageWidth - $textWidth) / 2;
        $y = $pageHeight / 2;

        // Add text without rotation (horizontal)
        $pdf->Text($x, $y, $text);

        // Add QR code if provided
        if ($qrCodePath && Storage::disk('public')->exists($qrCodePath)) {
            $this->addQRCodeToPage($pdf, $qrCodePath, $pageWidth, $pageHeight, $qrPosition);
        }
    }

    /**
     * Add QR code image to a page
     */
    private function addQRCodeToPage(Fpdi $pdf, string $qrCodePath, float $pageWidth, float $pageHeight, string $qrPosition): void
    {
        $qrFullPath = Storage::disk('public')->path($qrCodePath);

        // Calculate QR code position based on qr_position
        $qrSize = 50; // QR code size in mm
        $margin = 10; // Margin from edges

        switch ($qrPosition) {
            case 'top-left':
                $x = $margin;
                $y = $margin;
                break;
            case 'top-right':
                $x = $pageWidth - $qrSize - $margin;
                $y = $margin;
                break;
            case 'bottom-left':
                $x = $margin;
                $y = $pageHeight - $qrSize - $margin;
                break;
            case 'bottom-right':
                $x = $pageWidth - $qrSize - $margin;
                $y = $pageHeight - $qrSize - $margin;
                break;
            default:
                $x = $pageWidth - $qrSize - $margin;
                $y = $margin;
        }

        // Add QR code image
        $pdf->Image($qrFullPath, $x, $y, $qrSize, $qrSize);
    }

    /**
     * Clean up temporary files
     */
    public function cleanupTempFile(string $tempPath): void
    {
        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->delete($tempPath);
        }
    }
}

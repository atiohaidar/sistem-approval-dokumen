<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class PDFWatermarkService
{
    /**
     * Add watermark and QR code to PDF and return temporary file path
     */
    public function addWatermark(string $pdfPath, string $text = 'BELUM APPROVE', ?string $qrCodePath = null, $qrPosition = null): string
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
            $this->addWatermarkToPage($pdf, $text, $size['width'], $size['height'], $qrCodePath, $qrPosition, $pageNo);
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
    private function addWatermarkToPage(Fpdi $pdf, string $text, float $pageWidth, float $pageHeight, ?string $qrCodePath = null, $qrPosition = null, int $currentPage = 1): void
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

        // Add QR code if provided and this is the target page
        if ($qrCodePath && Storage::disk('public')->exists($qrCodePath)) {
            $targetPage = 1; // Default page

            // Parse QR position to get target page
            if (is_array($qrPosition) && isset($qrPosition['page'])) {
                $targetPage = $qrPosition['page'];
            }

            // Only add QR code to the target page
            if ($currentPage === $targetPage) {
                $this->addQRCodeToPage($pdf, $qrCodePath, $pageWidth, $pageHeight, $qrPosition);
            }
        }
    }

    /**
     * Add QR code image to a page
     */
    private function addQRCodeToPage(Fpdi $pdf, string $qrCodePath, float $pageWidth, float $pageHeight, $qrPosition): void
    {
        $qrFullPath = Storage::disk('public')->path($qrCodePath);

        // Calculate QR code position
        $qrSize = 50; // Default QR code size in mm (fallback)

        // Default position (top-right) for backward compatibility
        $x = $pageWidth - $qrSize - 10;
        $y = 90;

        // If new coordinate format is provided
        if (is_array($qrPosition)) {
            if (isset($qrPosition['size']) && is_numeric($qrPosition['size']) && $qrPosition['size'] > 0) {
                $qrSize = $qrPosition['size'] * $pageWidth; // normalize relative width (0-1) to page width
            }
            if (isset($qrPosition['x']) && isset($qrPosition['y'])) {
                // Convert relative coordinates (0.0-1.0) to absolute coordinates using center point
                $centerX = $qrPosition['x'] * $pageWidth;
                $centerY = $qrPosition['y'] * $pageHeight;

                // Translate center-based coordinates to top-left origin expected by FPDF
                $x = $centerX - ($qrSize / 2);
                $y = $centerY - ($qrSize / 2);

                // Ensure QR code stays within page bounds
                $x = max(0, min($x, $pageWidth - $qrSize));
                $y = max(0, min($y, $pageHeight - $qrSize));
            }
        } elseif (is_string($qrPosition)) {
            // Backward compatibility with old string format
            $margin = 10;
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
            }
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

<?php

namespace App\Services;

use App\Models\Document;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for document with approval status
     */
    public function generateForDocument(Document $document, string $position): string
    {
        // Generate URL to public document info
        $url = url('/api/documents/' . $document->id . '/public-info');

        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            size: 300, // Increased size for URL data
            margin: 10
        );

        $result = $builder->build();

        // Generate filename
        $filename = 'qr_' . $document->id . '_' . time() . '.png';
        $path = 'qr-codes/' . $filename;

        // Save to storage
        Storage::disk('public')->put($path, $result->getString());

        return $path;
    }

    /**
     * Get QR code URL for document
     */
    public function getQRUrl(Document $document): string
    {
        return url('/api/documents/' . $document->id . '/public-info');
    }

    /**
     * Update QR code when document status changes
     */
    public function updateQRCode(Document $document): string
    {
        // Remove old QR code if exists
        if ($document->qr_code_path && Storage::disk('public')->exists($document->qr_code_path)) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        // Generate new QR code
        $newQrCodePath = $this->generateForDocument($document, $document->qr_position ?? 'top-right');

        // Update document with new QR code path
        $document->update(['qr_code_path' => $newQrCodePath]);

        return $newQrCodePath;
    }
}



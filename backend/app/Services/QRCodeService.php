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
     * 
     * @param Document $document
     * @param array $position
     * @param string|null $accessToken If provided, generates secure URL with token
     */
    public function generateForDocument(Document $document, $position, ?string $accessToken = null): string
    {
        // Generate URL to frontend document page
        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        
        // Use secure token URL if provided, otherwise use legacy public URL
        if ($accessToken) {
            $url = $frontendBase . '/secure/' . $accessToken;
        } else {
            // Fallback to legacy public URL for backward compatibility
            $url = $frontendBase . '/public/' . $document->id;
        }

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
     * 
     * @param Document $document
     * @param string|null $accessToken If provided, generates secure URL with token
     */
    public function getQRUrl(Document $document, ?string $accessToken = null): string
    {
        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        
        if ($accessToken) {
            return $frontendBase . '/secure/' . $accessToken;
        }
        
        // Fallback to legacy public URL
        return $frontendBase . '/public/' . $document->id;
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

        // Generate new QR code with current coordinates
        $position = [
            'x' => $document->qr_x,
            'y' => $document->qr_y,
            'page' => $document->qr_page ?? 1,
        ];

        $newQrCodePath = $this->generateForDocument($document, $position);

        // Update document with new QR code path
        $document->update(['qr_code_path' => $newQrCodePath]);

        return $newQrCodePath;
    }
}



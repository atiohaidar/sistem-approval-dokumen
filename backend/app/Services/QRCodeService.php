<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Generate QR code for document.
     */
    public function generateForDocument(Document $document): array
    {
        // Create QR data
        $qrData = [
            'document_id' => $document->id,
            'title' => $document->title,
            'created_by' => $document->creator->name,
            'created_at' => $document->created_at->toISOString(),
            'status' => $document->getApprovalStatusForQr(),
            'url' => config('app.url') . '/api/documents/' . $document->id,
        ];

        // Generate QR code
        $qrCode = new QrCode(
            data: json_encode($qrData),
            size: 300,
            margin: 10
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        // Generate filename and path
        $filename = 'qr_' . $document->id . '_' . Str::random(8) . '.svg';
        $path = 'qr-codes/' . $filename;

        // Store QR code
        Storage::disk('public')->put($path, $result->getString());

        // Update document with QR code info
        $document->update([
            'qr_code_path' => $path,
            'qr_code_data' => $qrData,
        ]);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'data' => $qrData,
        ];
    }

    /**
     * Update QR code data when approval status changes.
     */
    public function updateForDocument(Document $document): array
    {
        if (!$document->hasQrCode()) {
            return $this->generateForDocument($document);
        }

        // Update QR data
        $qrData = [
            'document_id' => $document->id,
            'title' => $document->title,
            'created_by' => $document->creator->name,
            'created_at' => $document->created_at->toISOString(),
            'status' => $document->getApprovalStatusForQr(),
            'url' => config('app.url') . '/api/documents/' . $document->id,
        ];

        // Regenerate QR code with updated data
        $qrCode = new QrCode(json_encode($qrData));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        Storage::disk('public')->put($document->qr_code_path, $result->getString());

        // Update document
        $document->update(['qr_code_data' => $qrData]);

        return [
            'path' => $document->qr_code_path,
            'url' => Storage::disk('public')->url($document->qr_code_path),
            'data' => $qrData,
        ];
    }

    /**
     * Get QR code data for display.
     */
    public function getQRData(Document $document): array
    {
        if (!$document->hasQrCode()) {
            return $this->generateForDocument($document);
        }

        return [
            'document_id' => $document->id,
            'title' => $document->title,
            'creator' => $document->creator->name,
            'created_at' => $document->created_at->toISOString(),
            'status' => $document->getApprovalStatusForQr(),
            'qr_code_url' => $document->qr_code_path ? Storage::disk('public')->url($document->qr_code_path) : null,
            'qr_position' => $document->qr_position,
            'qr_position_label' => $document->getQrPositionLabel(),
        ];
    }

    /**
     * Validate QR position.
     */
    public static function getValidPositions(): array
    {
        return [
            'top-left' => 'Kiri Atas',
            'top-right' => 'Kanan Atas',
            'bottom-left' => 'Kiri Bawah',
            'bottom-right' => 'Kanan Bawah',
            'center' => 'Tengah',
        ];
    }

    /**
     * Check if position is valid.
     */
    public static function isValidPosition(string $position): bool
    {
        return in_array($position, array_keys(self::getValidPositions()));
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;

class RegenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:regenerate-qrs {--ids=} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for documents. Use --ids=1,2,3 to target specific documents.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ids = $this->option('ids');

        if ($ids) {
            $idArray = array_filter(array_map('trim', explode(',', $ids)));
            $query = Document::whereIn('id', $idArray);
            $total = $query->count();
        } else {
            if (!$this->option('force') && !$this->confirm('Regenerate QR for ALL documents? This will overwrite existing QR images. Continue?')) {
                $this->info('Aborted.');
                return 0;
            }
            $query = Document::query();
            $total = $query->count();
        }

        if ($total === 0) {
            $this->info('No documents found to process.');
            return 0;
        }

        $this->info("Processing $total documents...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(100, function ($documents) use ($bar) {
            foreach ($documents as $document) {
                try {
                    app(\App\Services\QRCodeService::class)->updateQRCode($document);
                } catch (\Exception $e) {
                    $this->error("\nFailed for document {$document->id}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Done.');

        return 0;
    }
}

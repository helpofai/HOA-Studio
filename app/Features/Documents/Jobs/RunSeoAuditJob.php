<?php

namespace App\Features\Documents\Jobs;

use App\Models\Document;
use App\Features\Documents\Actions\AnalyzeDocumentSeo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunSeoAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $documentId,
        public array $secondaryKeywords,
        public ?string $targetKeyword = null,
        public ?string $metaDescription = null
    ) {}

    public function handle(AnalyzeDocumentSeo $action): void
    {
        $document = Document::with('content')->findOrFail($this->documentId);

        $action->execute(
            $document,
            $this->targetKeyword,
            $this->secondaryKeywords,
            $this->metaDescription
        );
    }
}

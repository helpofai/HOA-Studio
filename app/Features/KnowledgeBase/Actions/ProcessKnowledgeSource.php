<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\KnowledgeBase\Actions;

use App\Features\KnowledgeBase\Models\KnowledgeChunk;
use App\Features\KnowledgeBase\Models\KnowledgeSource;
use App\Features\KnowledgeBase\Services\SemanticChunker;
use App\Features\KnowledgeBase\Services\VectorSearchEngine;

class ProcessKnowledgeSource
{
    public function __construct(
        protected SemanticChunker $chunker,
        protected VectorSearchEngine $vectorEngine
    ) {}

    public function execute(KnowledgeSource $source): void
    {
        $source->update(['status' => 'processing']);

        // Delete old chunks if re-processing
        $source->chunks()->delete();

        $chunksData = $this->chunker->chunk($source->content);

        foreach ($chunksData as $c) {
            $embedding = $this->vectorEngine->generateEmbedding(
                text: $c['content'],
                model: 'text-embedding-3-small',
                user: $source->user
            );

            KnowledgeChunk::create([
                'knowledge_source_id' => $source->id,
                'chunk_index' => $c['chunk_index'],
                'content' => $c['content'],
                'token_count' => $c['token_count'],
                'embedding_vector' => $embedding,
            ]);
        }

        $source->update(['status' => 'ready']);
    }
}
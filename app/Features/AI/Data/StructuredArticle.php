<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - StructuredArticle DTO
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\AI\Data;

/**
 * Structured Article JSON Model DTO
 * Serves as the canonical data model before rendering into ProseMirror / TipTap HTML.
 */
class StructuredArticle
{
    public function __construct(
        public array $metadata = [],
        public array $introduction = [],
        public array $sections = [],
        public array $faq = [],
        public array $conclusion = [],
        public array $references = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            metadata: $data['metadata'] ?? [],
            introduction: $data['introduction'] ?? [],
            sections: $data['sections'] ?? [],
            faq: $data['faq'] ?? [],
            conclusion: $data['conclusion'] ?? [],
            references: $data['references'] ?? []
        );
    }

    /**
     * Render the structured article model into clean HTML for TipTap canvas consumption.
     */
    public function toHtml(): string
    {
        $html = '';

        // Title
        if (! empty($this->metadata['title'])) {
            $html .= '<h1>'.e($this->metadata['title']).'</h1>'."\n\n";
        }

        // Quick Answer Direct Snippet Callout
        if (! empty($this->introduction['quick_answer'])) {
            $html .= '<div class="p-4 rounded-xl bg-violet-950/40 border border-violet-500/30 mb-6">'."\n";
            $html .= '<strong class="text-violet-300 text-xs uppercase tracking-wider block mb-1">💡 Quick Summary / Key Takeaway</strong>'."\n";
            $html .= '<p class="text-slate-200 text-sm leading-relaxed mb-0">'.$this->introduction['quick_answer'].'</p>'."\n";
            $html .= '</div>'."\n\n";
        }

        // Intro Paragraphs
        if (! empty($this->introduction['body'])) {
            $html .= $this->introduction['body']."\n\n";
        }

        // Sections
        foreach ($this->sections as $sec) {
            if (! empty($sec['heading'])) {
                $level = $sec['level'] ?? 2;
                $html .= "<h{$level}>".e($sec['heading'])."</h{$level}>\n\n";
            }
            if (! empty($sec['content'])) {
                $html .= $sec['content']."\n\n";
            }
        }

        // FAQ Section
        if (! empty($this->faq)) {
            $html .= "<h2>Frequently Asked Questions</h2>\n\n";
            foreach ($this->faq as $item) {
                if (! empty($item['question']) && ! empty($item['answer'])) {
                    $html .= '<h3>'.e($item['question']).'</h3>'."\n";
                    $html .= '<p>'.$item['answer'].'</p>'."\n\n";
                }
            }
        }

        // Conclusion
        if (! empty($this->conclusion['heading'])) {
            $html .= '<h2>'.e($this->conclusion['heading']).'</h2>'."\n\n";
        }
        if (! empty($this->conclusion['body'])) {
            $html .= $this->conclusion['body']."\n\n";
        }

        return trim($html);
    }

    public function toArray(): array
    {
        return [
            'metadata' => $this->metadata,
            'introduction' => $this->introduction,
            'sections' => $this->sections,
            'faq' => $this->faq,
            'conclusion' => $this->conclusion,
            'references' => $this->references,
        ];
    }
}

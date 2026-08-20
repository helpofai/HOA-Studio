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

namespace App\Features\Templates\Database\Seeders;

use App\Features\Templates\Models\Template;
use App\Features\Templates\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Blog & SEO',
                'slug' => 'blog-seo',
                'icon' => '📰',
                'description' => 'Long-form blog posts, SEO outlines, meta descriptions, and keyword optimization.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Marketing & Ads',
                'slug' => 'marketing-ads',
                'icon' => '🎯',
                'description' => 'High-converting landing pages, PAS copy, Google/Facebook ads, and value propositions.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Email & Outreach',
                'slug' => 'email-outreach',
                'icon' => '✉️',
                'description' => 'Cold email outreach sequences, product launch announcements, and newsletters.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Social Media',
                'slug' => 'social-media',
                'icon' => '📱',
                'description' => 'Viral LinkedIn posts, Twitter/X threads, YouTube video scripts, and hooks.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Business & Strategy',
                'slug' => 'business-strategy',
                'icon' => '💼',
                'description' => 'Executive summaries, problem-solution briefs, customer case studies, and proposals.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Technical & Code',
                'slug' => 'technical-code',
                'icon' => '💻',
                'description' => 'API documentation, architecture decision records, release notes, and technical guides.',
                'sort_order' => 6,
            ],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = TemplateCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        $templates = [
            // 1. Blog & SEO
            [
                'category_slug' => 'blog-seo',
                'name' => 'Complete SEO Long-Form Article',
                'slug' => 'seo-longform-article',
                'icon' => '🚀',
                'description' => 'Produces a comprehensive, highly-structured 1500+ word SEO article with H2/H3 headings, meta tags, and FAQ section.',
                'system_instructions' => 'You are an elite SEO content strategist. Write in-depth, original, engaging, and actionable content structured with markdown headings, bullet points, and key takeaways.',
                'prompt_template' => "Write an authoritative and comprehensive SEO-optimized long-form article about: {{topic}}\n\nTarget Audience: {{audience}}\nPrimary & Secondary Keywords: {{keywords}}\nKey Takeaways / Unique Angle: {{angle}}\n\nStructure Requirements:\n1. Click-Worthy SEO Title & Meta Description\n2. Engaging Hook & Problem Introduction\n3. Comprehensive Core Sections with clear H2 and H3 subheadings\n4. Actionable Steps and Concrete Examples\n5. Frequently Asked Questions (FAQ) Section with Schema-ready answers\n6. Compelling Conclusion with Call-to-Action",
                'inputs_schema' => [
                    ['name' => 'topic', 'label' => 'Article Topic / Title', 'type' => 'text', 'placeholder' => 'e.g. The Future of AI Agents in B2B Customer Support', 'required' => true],
                    ['name' => 'audience', 'label' => 'Target Audience', 'type' => 'text', 'placeholder' => 'e.g. SaaS Founders, Operations Managers', 'required' => true],
                    ['name' => 'keywords', 'label' => 'Target SEO Keywords', 'type' => 'text', 'placeholder' => 'e.g. AI agents, customer service automation, LLM workflows', 'required' => false],
                    ['name' => 'angle', 'label' => 'Unique Angle / Core Points', 'type' => 'textarea', 'placeholder' => 'e.g. Emphasize multi-agent autonomy vs single chatbot prompts', 'required' => false],
                ],
            ],
            // 2. Marketing & Ads
            [
                'category_slug' => 'marketing-ads',
                'name' => 'High-Converting Landing Page Copy',
                'slug' => 'landing-page-copy',
                'icon' => '💎',
                'description' => 'Generates full conversion-focused landing page copy: Hero headline, subheadline, pain points, features, social proof, and CTA.',
                'system_instructions' => 'You are a world-class conversion copywriter. Use direct-response copywriting principles that evoke urgency, highlight transformative benefits, and overcome objections.',
                'prompt_template' => "Create high-converting landing page copy for: {{product_name}}\n\nProduct Description & Value Proposition: {{product_description}}\nTarget Customer: {{target_customer}}\nPrimary Problem Solved: {{core_problem}}\nCall to Action: {{cta}}\n\nInclude:\n1. Hero Section (H1 Headline, Subheadline, Primary CTA Button, Trust Badge)\n2. The Problem / Agitation (3 major customer pain points)\n3. The Solution & 3 Key Benefit Pillars (Feature + Benefit breakdown)\n4. Social Proof & Testimonial placeholders\n5. Overcoming Objections (FAQ micro-copy)\n6. Final High-Impact Urgency CTA",
                'inputs_schema' => [
                    ['name' => 'product_name', 'label' => 'Product / Service Name', 'type' => 'text', 'placeholder' => 'e.g. HelpOfAi Studio', 'required' => true],
                    ['name' => 'product_description', 'label' => 'What does it do?', 'type' => 'textarea', 'placeholder' => 'e.g. AI-powered content orchestration workspace with 400+ models', 'required' => true],
                    ['name' => 'target_customer', 'label' => 'Target Customer Persona', 'type' => 'text', 'placeholder' => 'e.g. Growth Marketers, Agencies, Copywriters', 'required' => true],
                    ['name' => 'core_problem', 'label' => 'Main Pain Point Solved', 'type' => 'text', 'placeholder' => 'e.g. Juggling multiple AI subscriptions and slow drafting', 'required' => true],
                    ['name' => 'cta', 'label' => 'Desired Call to Action', 'type' => 'text', 'placeholder' => 'e.g. Start Free 14-Day Trial', 'required' => false],
                ],
            ],
            // 3. Email & Outreach
            [
                'category_slug' => 'email-outreach',
                'name' => '3-Touch B2B Cold Email Sequence',
                'slug' => 'cold-email-sequence',
                'icon' => '📬',
                'description' => 'Crafts a high-reply 3-step cold outreach campaign: Personalized hook, value proposition, and frictionless follow-up.',
                'system_instructions' => 'You are an expert sales development copywriter. Keep cold emails under 120 words each, ultra-personalized, authentic, and ending with a low-friction question.',
                'prompt_template' => "Write a 3-touch cold email outreach sequence offering: {{offer}}\n\nProspect Role / ICP: {{prospect_role}}\nCompany / Industry: {{industry}}\nKey Metric / Transformation: {{key_metric}}\n\nGenerate:\n- Email 1: Initial Hook & Pain Point (under 100 words)\n- Email 2: Value Demonstration & Case Reference (Day 3 follow-up)\n- Email 3: The 9-word low-pressure breakup email (Day 7 follow-up)\nProvide 2 compelling subject line variants per email.",
                'inputs_schema' => [
                    ['name' => 'offer', 'label' => 'What are you offering / pitching?', 'type' => 'textarea', 'placeholder' => 'e.g. AI workflow audit that reduces content production costs by 60%', 'required' => true],
                    ['name' => 'prospect_role', 'label' => 'Prospect Job Title', 'type' => 'text', 'placeholder' => 'e.g. VP of Marketing, Head of Content', 'required' => true],
                    ['name' => 'industry', 'label' => 'Industry / Niche', 'type' => 'text', 'placeholder' => 'e.g. FinTech SaaS, E-commerce Brands', 'required' => true],
                    ['name' => 'key_metric', 'label' => 'Key Metric or Result', 'type' => 'text', 'placeholder' => 'e.g. 3x faster content publishing with 0 added headcount', 'required' => false],
                ],
            ],
            // 4. Social Media
            [
                'category_slug' => 'social-media',
                'name' => 'Viral LinkedIn Story Post',
                'slug' => 'viral-linkedin-post',
                'icon' => '💡',
                'description' => 'Transforms an insight or lesson into a formatted LinkedIn post with high-converting hook, story, and conversation starter.',
                'system_instructions' => 'You are a top 1% LinkedIn creator. Use short line breaks, punchy opening lines (before the See More fold), authentic storytelling, and a thought-provoking closing question.',
                'prompt_template' => "Write an engaging, viral-formatted LinkedIn post about: {{core_lesson}}\n\nContext / Personal Story: {{context}}\nTarget Audience: {{audience}}\n\nFormatting:\n1. Hook (2 punchy 1-line openers with curiosity gap)\n2. The Turning Point / Challenge\n3. The 3 Core Lessons / Framework (use concise bullet points)\n4. The Big Realization / Takeaway\n5. Conversation-starting question for the comments section",
                'inputs_schema' => [
                    ['name' => 'core_lesson', 'label' => 'Core Insight or Lesson', 'type' => 'textarea', 'placeholder' => 'e.g. Why hiring more writers slowed our agency down until we built AI templates', 'required' => true],
                    ['name' => 'context', 'label' => 'Background / Numbers / Story', 'type' => 'textarea', 'placeholder' => 'e.g. Spent $20k on freelancers vs $200 on LLM automation', 'required' => false],
                    ['name' => 'audience', 'label' => 'Target Audience', 'type' => 'text', 'placeholder' => 'e.g. Agency Owners, Founders, Tech Leaders', 'required' => false],
                ],
            ],
            // 5. Business & Strategy
            [
                'category_slug' => 'business-strategy',
                'name' => 'Executive Problem-Solution Brief',
                'slug' => 'executive-problem-solution-brief',
                'icon' => '📊',
                'description' => 'Prepares a polished 1-page executive brief covering background, problem statement, options, recommendation, and ROI.',
                'system_instructions' => 'You are a McKinsey-level management consultant. Produce crisp, structured, data-driven executive briefings with zero fluff.',
                'prompt_template' => "Prepare a formal Executive Brief on: {{initiative}}\n\nCurrent Business Challenge: {{problem}}\nProposed Solution: {{solution}}\nExpected ROI & Timeline: {{roi_timeline}}\n\nStructure:\n- Executive Summary (3 sentences)\n- Problem Analysis & Cost of Inaction\n- Strategic Recommendation & Milestones\n- Risk Mitigation & Resource Allocation",
                'inputs_schema' => [
                    ['name' => 'initiative', 'label' => 'Project / Initiative Title', 'type' => 'text', 'placeholder' => 'e.g. AI-First Customer Knowledge Base Migration', 'required' => true],
                    ['name' => 'problem', 'label' => 'Core Problem & Impact', 'type' => 'textarea', 'placeholder' => 'e.g. Support ticket resolution time is 48 hours, causing churn', 'required' => true],
                    ['name' => 'solution', 'label' => 'Proposed Solution', 'type' => 'textarea', 'placeholder' => 'e.g. Deploy RAG knowledge assistant for instant tier-1 resolution', 'required' => true],
                    ['name' => 'roi_timeline', 'label' => 'ROI Expectations & Timeline', 'type' => 'text', 'placeholder' => 'e.g. $120k annual savings, 60-day rollout', 'required' => false],
                ],
            ],
            // 6. Technical & Code
            [
                'category_slug' => 'technical-code',
                'name' => 'API Documentation & Integration Guide',
                'slug' => 'api-documentation-guide',
                'icon' => '⚡',
                'description' => 'Generates developer-friendly API endpoint documentation with parameter tables, curl examples, and error responses.',
                'system_instructions' => 'You are a principal technical writer. Write accurate, clean markdown documentation with copy-pasteable code examples and structured parameter tables.',
                'prompt_template' => "Write complete developer API documentation for endpoint: {{endpoint_name}}\n\nHTTP Method & Path: {{http_method}} {{endpoint_path}}\nPurpose: {{purpose}}\nRequest Parameters: {{parameters}}\nSample Request & Response: {{sample_payload}}\n\nInclude:\n- Endpoint Description & Authentication Requirements\n- Request Headers & Body Parameters Table (Type, Required, Description)\n- cURL, JavaScript Fetch, and Python requests examples\n- 200 OK JSON Response schema\n- Common Error Codes (400, 401, 429, 500) and troubleshooting steps",
                'inputs_schema' => [
                    ['name' => 'endpoint_name', 'label' => 'Endpoint Name', 'type' => 'text', 'placeholder' => 'e.g. Create Chat Completion Stream', 'required' => true],
                    ['name' => 'http_method', 'label' => 'HTTP Method', 'type' => 'text', 'placeholder' => 'POST', 'required' => true],
                    ['name' => 'endpoint_path', 'label' => 'Endpoint Path', 'type' => 'text', 'placeholder' => '/v1/chat/completions', 'required' => true],
                    ['name' => 'purpose', 'label' => 'Endpoint Purpose', 'type' => 'textarea', 'placeholder' => 'e.g. Streams SSE tokens for LLM generation', 'required' => true],
                    ['name' => 'parameters', 'label' => 'Key Parameters', 'type' => 'textarea', 'placeholder' => 'e.g. model (string), messages (array), temperature (float)', 'required' => false],
                    ['name' => 'sample_payload', 'label' => 'Sample Request/Response', 'type' => 'textarea', 'placeholder' => 'e.g. {"model": "auto", "messages": [{"role": "user", "content": "hello"}]}', 'required' => false],
                ],
            ],
        ];

        foreach ($templates as $tmpl) {
            $catSlug = $tmpl['category_slug'];
            $cat = $catModels[$catSlug] ?? null;

            if ($cat) {
                Template::updateOrCreate(
                    ['slug' => $tmpl['slug']],
                    [
                        'template_category_id' => $cat->id,
                        'name' => $tmpl['name'],
                        'description' => $tmpl['description'],
                        'icon' => $tmpl['icon'],
                        'prompt_template' => $tmpl['prompt_template'],
                        'system_instructions' => $tmpl['system_instructions'],
                        'inputs_schema' => $tmpl['inputs_schema'],
                        'is_system' => true,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Dynamic Template Mail Delivery Mailable
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

namespace App\Features\Admin\Mail;

use App\Features\Admin\Services\DynamicMailConfigService;
use App\Features\Admin\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplateMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;
    public string $renderedHeading;
    public string $renderedBody;
    public string $renderedActionText;
    public string $renderedActionUrl;

    public function __construct(
        public string $templateKey,
        public array $variables = []
    ) {
        DynamicMailConfigService::applyDatabaseMailConfig();

        $compiled = MailTemplateService::getCompiledTemplate($this->templateKey);

        $this->renderedSubject = MailTemplateService::render($compiled['subject'], $this->variables);
        $this->renderedHeading = MailTemplateService::render($compiled['heading'], $this->variables);
        $this->renderedBody = MailTemplateService::render($compiled['body'], $this->variables);
        $this->renderedActionText = MailTemplateService::render($compiled['action_text'], $this->variables);
        $this->renderedActionUrl = MailTemplateService::render($compiled['action_url'], $this->variables);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templated-system-mail',
        );
    }
}

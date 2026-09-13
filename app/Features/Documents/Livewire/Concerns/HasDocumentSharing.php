<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Concern: Document Sharing
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

namespace App\Features\Documents\Livewire\Concerns;

use App\Features\Documents\Actions\CreateDocumentShare;
use App\Features\Documents\Actions\RevokeDocumentShare;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentShare;
use App\Features\Documents\Contracts\EditorRegistry;
use Illuminate\Support\Facades\Auth;

trait HasDocumentSharing
{
    public function switchEditorType(string $type = '', string $newType = '')
    {
        $target = ! empty($type) ? $type : $newType;
        if (EditorRegistry::isValidEditor($target)) {
            $this->editorType = $target;
            Document::where('id', $this->documentId)->update(['editor_type' => $target]);
            $this->dispatch('editor:reload', editorType: $target);
        }
    }

    public function openShareModal()
    {
        $this->loadShareState();
        $this->showShareModal = true;
    }

    public function loadShareState()
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $this->shareToken = $share->share_token;
            $this->isShareActive = true;
            $this->shareAllowCopy = $share->allow_copy;
            $this->shareAllowDownload = $share->allow_download;
            $this->shareViewCount = $share->view_count;
            $this->shareUrl = route('public.share', ['token' => $share->share_token]);
        } else {
            $this->shareToken = null;
            $this->isShareActive = false;
            $this->shareUrl = '';
        }
    }

    public function createOrUpdateShare(CreateDocumentShare $action)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($this->documentId);

        $share = $action->execute($document, [
            'password' => ! empty($this->sharePassword) ? $this->sharePassword : null,
            'allow_copy' => $this->shareAllowCopy,
            'allow_download' => $this->shareAllowDownload,
            'expires_in_days' => $this->shareExpiryDays,
        ]);

        $this->loadShareState();
        session()->flash('share_status', 'Share link generated successfully!');
    }

    public function revokeShare(RevokeDocumentShare $action)
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $action->execute($share);
        }

        $this->loadShareState();
        session()->flash('share_status', 'Share link has been revoked.');
    }
}

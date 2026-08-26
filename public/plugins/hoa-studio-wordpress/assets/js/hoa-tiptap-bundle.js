/**
 * HOA Studio TipTap Engine & Streaming Bridge for WordPress
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $canvas = $('#hoa-wp-tiptap-target');
        const $hiddenInput = $('#hoa_tiptap_html_content');
        const $wordCount = $('#hoa-wp-word-count');
        const $readingTime = $('#hoa-wp-reading-time');
        const $aiBar = $('#hoa-wp-ai-bar');
        const $streamingStatus = $('#hoa-wp-streaming-indicator');
        let activeEventSource = null;

        if (!$canvas.length) {
            return;
        }

        function updateStats() {
            const text = $canvas.text() || '';
            const words = text.trim().split(/\s+/).filter(Boolean).length;
            $wordCount.text(words.toLocaleString());
            $readingTime.text(Math.max(1, Math.ceil(words / 200)) + 'm');
            $hiddenInput.val($canvas.html());
        }

        // Live typing input stats
        $canvas.on('input', updateStats);
        updateStats();

        // Toolbar formatting commands
        $('.hoa-tool-btn').on('click', function(e) {
            e.preventDefault();
            const cmd = $(this).data('cmd');
            document.execCommand(getExecCmd(cmd), false, null);
            updateStats();
        });

        function getExecCmd(cmd) {
            switch(cmd) {
                case 'bold': return 'bold';
                case 'italic': return 'italic';
                case 'underline': return 'underline';
                case 'strike': return 'strikeThrough';
                case 'bulletList': return 'insertUnorderedList';
                case 'orderedList': return 'insertOrderedList';
                case 'undo': return 'undo';
                case 'redo': return 'redo';
                case 'heading1': return 'formatBlock';
                case 'heading2': return 'formatBlock';
                case 'heading3': return 'formatBlock';
                default: return 'bold';
            }
        }

        // AI Command Bar Toggle
        $('#hoa-wp-btn-ask-ai').on('click', function(e) {
            e.preventDefault();
            $aiBar.slideToggle(150, function() {
                if ($aiBar.is(':visible')) {
                    $('#hoa-wp-ai-prompt-input').focus();
                }
            });
        });

        $('#hoa-wp-ai-close-btn').on('click', function() {
            $aiBar.slideUp(150);
        });

        // Keyboard Shortcut Ctrl+K
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#hoa-wp-btn-ask-ai').trigger('click');
            }
        });

        // Submit AI Generation (SSE Streaming)
        $('#hoa-wp-ai-submit-btn').on('click', function() {
            const prompt = $('#hoa-wp-ai-prompt-input').val();
            const type = $('#hoa-wp-ai-type-select').val();
            const selectedText = window.getSelection().toString() || $canvas.text();

            if (!prompt && !selectedText) {
                alert('Please type an instruction or select text in the editor.');
                return;
            }

            $streamingStatus.show();

            const formData = new FormData();
            formData.append('action', 'hoa_studio_stream_proxy');
            formData.append('nonce', hoaStudioConfig.nonce);
            formData.append('text', selectedText || prompt);
            formData.append('type', type);
            formData.append('custom_instruction', prompt);

            fetch(hoaStudioConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
            }).then(response => {
                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');

                function readStream() {
                    reader.read().then(({ done, value }) => {
                        if (done) {
                            $streamingStatus.hide();
                            updateStats();
                            return;
                        }

                        const chunk = decoder.decode(value, { stream: true });
                        const lines = chunk.split('\n');

                        lines.forEach(line => {
                            if (line.startsWith('data: ')) {
                                try {
                                    const json = JSON.parse(line.substring(6));
                                    if (json.delta) {
                                        $canvas.append(json.delta);
                                        updateStats();
                                    }
                                } catch(err) {}
                            }
                        });

                        readStream();
                    });
                }

                readStream();
            }).catch(err => {
                $streamingStatus.hide();
                alert('Streaming failed: ' + err.message);
            });
        });

        // Fullscreen Toggle
        $('#hoa-wp-btn-fullscreen').on('click', function() {
            $('.hoa-wp-editor-wrapper').toggleClass('hoa-fullscreen');
        });
    });
})(jQuery);

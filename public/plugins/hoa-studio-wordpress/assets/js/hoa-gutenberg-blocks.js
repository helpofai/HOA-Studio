/**
 * HOA Studio Gutenberg Custom Blocks Extension
 */

(function(blocks, element, editor, components, i18n) {
    const el = element.createElement;
    const { registerBlockType } = blocks;
    const { RichText, InspectorControls } = editor;
    const { PanelBody, TextControl, SelectControl, Button } = components;
    const { __ } = i18n;

    registerBlockType('hoa-studio/ai-content-generator', {
        title: __('HOA Studio AI Assistant', 'hoa-studio'),
        icon: 'superhero',
        category: 'widgets',
        attributes: {
            content: {
                type: 'string',
                source: 'html',
                selector: 'div.hoa-ai-generated-content',
            },
            prompt: {
                type: 'string',
                default: '',
            },
            taskType: {
                type: 'string',
                default: 'generate',
            },
        },

        edit: function(props) {
            const { attributes: { content, prompt, taskType }, setAttributes } = props;

            function runAiGeneration() {
                if (!prompt) return;

                const formData = new FormData();
                formData.append('action', 'hoa_studio_stream_proxy');
                formData.append('nonce', hoaStudioConfig.nonce);
                formData.append('text', prompt);
                formData.append('type', taskType);
                formData.append('custom_instruction', prompt);

                fetch(hoaStudioConfig.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                }).then(res => {
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder('utf-8');
                    let accumulated = '';

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                setAttributes({ content: accumulated });
                                return;
                            }
                            const chunk = decoder.decode(value, { stream: true });
                            chunk.split('\n').forEach(line => {
                                if (line.startsWith('data: ')) {
                                    try {
                                        const json = JSON.parse(line.substring(6));
                                        if (json.delta) {
                                            accumulated += json.delta;
                                            setAttributes({ content: accumulated });
                                        }
                                    } catch(e) {}
                                }
                            });
                            read();
                        });
                    }
                    read();
                });
            }

            return [
                el(InspectorControls, { key: 'controls' },
                    el(PanelBody, { title: __('HOA Studio AI Controls', 'hoa-studio') },
                        el(SelectControl, {
                            label: __('AI Action Type', 'hoa-studio'),
                            value: taskType,
                            options: [
                                { label: 'Write Section', value: 'generate' },
                                { label: 'Expand Content', value: 'expand' },
                                { label: 'Summarize Key Takeaways', value: 'summarize' },
                                { label: 'Simplify Text', value: 'simplify' },
                            ],
                            onChange: (val) => setAttributes({ taskType: val }),
                        })
                    )
                ),
                el('div', { className: 'hoa-gutenberg-ai-block-editor', style: { padding: '20px', border: '1px solid #6366f1', borderRadius: '12px', background: '#0f172a', color: '#fff' } },
                    el('div', { style: { display: 'flex', gap: '8px', marginBottom: '12px' } },
                        el(TextControl, {
                            placeholder: __('Ask HOA Studio AI...', 'hoa-studio'),
                            value: prompt,
                            onChange: (val) => setAttributes({ prompt: val }),
                            style: { flex: 1 }
                        }),
                        el(Button, {
                            isPrimary: true,
                            onClick: runAiGeneration,
                        }, __('Generate AI Stream', 'hoa-studio'))
                    ),
                    el(RichText, {
                        tagName: 'div',
                        className: 'hoa-ai-generated-content',
                        placeholder: __('AI stream will render here...', 'hoa-studio'),
                        value: content,
                        onChange: (val) => setAttributes({ content: val }),
                        style: { minHeight: '80px', color: '#e2e8f0' }
                    })
                )
            ];
        },

        save: function(props) {
            return el(RichText.Content, {
                tagName: 'div',
                className: 'hoa-ai-generated-content',
                value: props.attributes.content,
            });
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.i18n
);

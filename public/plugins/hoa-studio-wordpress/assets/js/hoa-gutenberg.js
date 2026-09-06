/**
 * HOA-Studio WordPress Plugin - Gutenberg AI Copilot Sidebar Extension
 * Copyright (c) 2026 Rajib Adhikary / HelpOfAi (HOA)
 */

(function(wp) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { createElement: el, useState } = wp.element;
    const { PanelBody, Button, TextareaControl, SelectControl, Spinner } = wp.components;
    const { dispatch, select } = wp.data;

    const config = window.hoaGutenbergConfig || {};

    const HoaSidebarComponent = () => {
        const [prompt, setPrompt] = useState('');
        const [model, setModel] = useState(config.defaultModel || 'auto');
        const [isLoading, setIsLoading] = useState(false);
        const [resultText, setResultText] = useState('');

        const modelOptions = [
            { label: '⚡ Auto (OmniRoute Smart Gateway)', value: 'auto' },
            ...(config.availableModels || []).map(m => ({
                label: `${m.name} (${m.provider || 'OmniRoute'})`,
                value: m.model_id
            }))
        ];

        const handleGenerate = () => {
            if (!prompt.trim()) {
                alert('Please enter an AI prompt instruction first.');
                return;
            }

            setIsLoading(true);
            setResultText('');

            jQuery.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hoa_studio_transform_proxy',
                    nonce: config.nonce,
                    text: prompt,
                    type: 'generate',
                    model: model,
                    custom_instruction: prompt
                },
                success: function(res) {
                    setIsLoading(false);
                    if (res.success && res.data && res.data.result) {
                        setResultText(res.data.result);
                    } else {
                        alert(res.data ? res.data.message : 'AI generation error');
                    }
                },
                error: function() {
                    setIsLoading(false);
                    alert('Network error communicating with HOA Studio.');
                }
            });
        };

        const handleInsertBlock = () => {
            if (!resultText) return;
            const blocks = wp.blocks.rawHandler({ HTML: resultText });
            dispatch('core/block-editor').insertBlocks(blocks);
            setResultText('');
            setPrompt('');
        };

        return el(
            PluginSidebar,
            {
                name: 'hoa-studio-sidebar',
                title: '⚡ HOA Studio AI',
                icon: 'star-filled'
            },
            el(
                PanelBody,
                { title: 'AI Copilot Assistant', initialOpen: true },
                el(SelectControl, {
                    label: 'AI Model',
                    value: model,
                    options: modelOptions,
                    onChange: (val) => setModel(val)
                }),
                el(TextareaControl, {
                    label: 'Prompt Instruction',
                    value: prompt,
                    placeholder: 'Write an authoritative intro on...',
                    onChange: (val) => setPrompt(val)
                }),
                el(
                    Button,
                    {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading,
                        onClick: handleGenerate,
                        style: { width: '100%', marginBottom: '12px' }
                    },
                    isLoading ? 'Streaming Intelligence...' : '⚡ Generate Content'
                ),
                resultText && el(
                    'div',
                    { style: { marginTop: '14px', background: '#0f172a', padding: '10px', borderRadius: '8px', color: '#f8fafc', fontSize: '12px' } },
                    el('p', { style: { maxHeight: '140px', overflowY: 'auto' } }, resultText),
                    el(
                        Button,
                        {
                            isSecondary: true,
                            onClick: handleInsertBlock,
                            style: { width: '100%', marginTop: '8px' }
                        },
                        '📥 Insert into Article'
                    )
                )
            )
        );
    };

    registerPlugin('hoa-studio-sidebar', {
        render: HoaSidebarComponent,
        icon: 'star-filled'
    });
})(window.wp);

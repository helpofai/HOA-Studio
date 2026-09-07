{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Version History & Snapshots Tab
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
--}}

<!-- ─── TAB 8: SNAPSHOT VERSIONS TIMELINE & TIME-MACHINE DIFF ───────── -->
<div x-show="rightTab === 'versions'" class="space-y-3" style="display: none;"
     x-data="{
         selectedSnapshot: null,
         showSnapshotDiff: false,
         snapshotDiffHtml: '',
         diffLoadingId: null,
         async compareSnapshot(vId, vNum) {
             this.diffLoadingId = vId;
             try {
                 const html = await $wire.getVersionContent(vId);
                 this.selectedSnapshot = { id: vId, version_number: vNum, content_html: html };
                 const ed = (typeof getEditor === 'function' ? getEditor() : null) || window.hoaEditorInstance;
                 const liveHtml = ed && typeof ed.getHTML === 'function' ? ed.getHTML() : '';
                 if (typeof computeWordDiff === 'function') {
                     this.snapshotDiffHtml = computeWordDiff(html, liveHtml).unifiedHtml;
                 } else {
                     this.snapshotDiffHtml = html;
                 }
                 this.showSnapshotDiff = true;
             } catch (e) {
                 console.error('Error diffing snapshot:', e);
             } finally {
                 this.diffLoadingId = null;
             }
         }
     }">
    <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-2.5 shadow-inner">
        <div class="flex items-center justify-between pb-1 border-b border-white/5">
            <span class="text-xs font-bold text-white flex items-center gap-1.5">
                <span class="text-indigo-400">🕒</span>
                <span>Version History & Time-Machine</span>
            </span>
            <button
                type="button"
                wire:click="saveExplicitSnapshot"
                wire:loading.attr="disabled"
                wire:target="saveExplicitSnapshot"
                class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-mono text-[10px] font-bold shadow-md cursor-pointer flex items-center gap-1"
            >
                <span wire:loading.remove wire:target="saveExplicitSnapshot">+ New Snapshot</span>
                <span wire:loading wire:target="saveExplicitSnapshot" class="inline-block animate-spin text-[9px]">⏳ Saving...</span>
            </button>
        </div>

        <!-- Interactive Snapshot Version Diff Modal / Flyout -->
        <div x-show="showSnapshotDiff && selectedSnapshot" class="p-3 rounded-xl bg-slate-950 border border-indigo-500/40 space-y-2.5" style="display: none;">
            <div class="flex items-center justify-between border-b border-white/10 pb-1.5 select-none">
                <div class="flex items-center gap-1.5 font-bold text-xs text-white">
                    <span>🔍 Comparing vs</span>
                    <span class="text-indigo-400 font-mono" x-text="'Version #' + selectedSnapshot?.version_number"></span>
                </div>
                <button type="button" x-on:click="showSnapshotDiff = false; selectedSnapshot = null; snapshotDiffHtml = '';" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕ Close</button>
            </div>

            <p class="text-[10px] text-slate-400 leading-snug">Review differences between your live canvas and this snapshot:</p>

            <!-- Word Level Diff Box -->
            <div class="max-h-48 overflow-y-auto hoa-custom-scrollbar p-2 rounded-lg bg-slate-900/90 border border-white/5 font-mono text-[11px] leading-relaxed select-text" x-html="snapshotDiffHtml || '<span class=\'text-slate-500\'>No changes detected.</span>'"></div>

            <div class="flex items-center justify-between pt-1 border-t border-white/5 font-mono text-[10.5px]">
                <button
                    type="button"
                    x-on:click="$wire.restoreVersion(selectedSnapshot.id); showSnapshotDiff = false; snapshotDiffHtml = '';"
                    class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition-colors cursor-pointer"
                >
                    ✓ Restore This Snapshot
                </button>
                <button
                    type="button"
                    x-on:click="showSnapshotDiff = false; selectedSnapshot = null; snapshotDiffHtml = '';"
                    class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 transition-colors cursor-pointer"
                >
                    Keep Current Live
                </button>
            </div>
        </div>

        <!-- Snapshot List -->
        <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
            @forelse($document->versions as $v)
                <div wire:key="doc-version-{{ $v->id }}" class="p-3 rounded-xl {{ $document->current_version_id === $v->id ? 'bg-indigo-600/20 border border-indigo-500/40' : 'bg-slate-950/80 border border-white/5' }} space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white text-[11px]">Version #{{ $v->version_number }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $v->created_at->format('M d, H:i') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-300 truncate">{{ $v->summary ?? 'Saved snapshot' }}</p>
                    <div class="flex items-center justify-between pt-1.5 text-[10px] text-slate-400 font-mono border-t border-white/5">
                        <span>{{ number_format($v->word_count) }} words</span>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                x-on:click="compareSnapshot({{ $v->id }}, {{ $v->version_number }})"
                                :disabled="diffLoadingId === {{ $v->id }}"
                                class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer flex items-center gap-1"
                                title="Compare snapshot diff against current canvas"
                            >
                                <span x-show="diffLoadingId === {{ $v->id }}" class="animate-spin text-[9px]">⏳</span>
                                <span>🔍 Diff</span>
                            </button>
                            @if($document->current_version_id !== $v->id)
                                <button
                                    type="button"
                                    wire:click="restoreVersion({{ $v->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="restoreVersion({{ $v->id }})"
                                    class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold transition-colors cursor-pointer flex items-center gap-1"
                                >
                                    <span wire:loading wire:target="restoreVersion({{ $v->id }})" class="animate-spin text-[9px]">⏳</span>
                                    <span>Restore</span>
                                </button>
                            @else
                                <span class="text-emerald-400 font-bold">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-[11px] italic py-1">No saved snapshots yet.</p>
            @endforelse
        </div>
    </div>
</div>

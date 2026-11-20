<div class="sticky bottom-[calc(4.25rem+env(safe-area-inset-bottom))] z-20 flex items-center justify-end gap-2 rounded-2xl bg-card/95 p-2 shadow-lg ring-1 ring-stone-200 backdrop-blur lg:bottom-3">
    <a href="{{ $cancel }}" class="a-btn a-btn-ghost flex-1 sm:flex-none">Cancel</a>
    <button class="a-btn a-btn-primary flex-1 sm:flex-none">{{ $label ?? 'Save' }}</button>
</div>

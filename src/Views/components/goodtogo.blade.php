@if(!Package\DocTalk\Services\LLMUtilities::goodToGo())
    <div
        wire:ignore
        x-transition.opacity.duration.500ms
        class="goodtogo-message"
    >
        <div class="message flex alignCenter gap-x-2">
            <x-doctalk::icons.info/>
            Sorry, ability to talk with documents is disabled.
        </div>
    </div>
@endif

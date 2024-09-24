<div x-data="
{
    done: true,
    interval: null,
    scrollToBottom() {
        if (this.$refs.scrollPoint) {
            this.$refs.scrollPoint.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }
}
" x-init="

    scrollToBottom();

    $wire.on('getAIResponse', () => {
        scrollToBottom();
        done = false;

        //Livewire.dispatch('showLoading');

        // Clear any existing interval before setting a new one
        if (this.interval) {
            clearInterval(this.interval);
        }

        this.interval = setInterval(() => scrollToBottom(), 100);
    });

    $wire.on('focusInput', () => {
        done = true;

        if (this.interval) {
            clearInterval(this.interval);
        }

        this.interval = setInterval(() => scrollToBottom(), 100);

        setTimeout(() => {
            clearInterval(this.interval);
            this.interval = null;
        }, 1000);

        //Livewire.dispatch('hideLoading');
    });
">

    <!-- Loading indicator -->
    <div wire:loading style="width: 100%; height: 100%; z-index: 10000">
        {!! $this->placeholder() !!}
    </div>

    @if (isset($conversation) && $conversation->exists)
        <div class="flex justifyCenter alignCenter" wire:ignore x-data x-init="scrollToBottom">
                <span
                    style="background: #F7F8F9; padding: 10px; border-radius: 10px; margin: 20px; color: #666; border:1px solid #eaebed">
                    📅 Conversation created {{$conversation->created_at->diffForHumans()}}
                </span>
        </div>
    @else
        <div class="flex justifyCenter" wire:ignore>
            <div
                style="
                    position: absolute;
                    top:30%;
                    background: #f7f8f9;
                    text-align: left;
                    padding: 20px;
                    border-radius: 10px;
                    font-size: 16px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    color: #555;
                    border:1px solid #eaebed;
                    ">
                <strong>🎉 Tips</strong>

                <ul style="list-style: square; margin: 20px 0 0 20px; line-height: 2rem;">
                    <li>For best results, always discuss each different case in a new conversation.</li>
                    <li>When asking questions, be specific mentioning case numbers or other relevant details.</li>
                    <li>Provide the most accurate information to get the best possible response.</li>
                </ul>
            </div>
        </div>
    @endif

    @if (count($this->messages) > 1)
        <div
            wire:ignore
            x-cloak
            class="topbuttons"
            style="display: flex; justify-content: flex-end; align-items: center; position: fixed; top: 4rem; right: 2.5rem; gap: 0.5rem; z-index: 100;">

            <!-- Clear Conversation Button -->
            <div x-data="{ isConfirming: false }" class="tooltip-wrapper tooltip t-top pointer"
                 data-tooltip-text="Clear Conversation">

                <style>
                    .topbuttons button:hover, .topbuttons a:hover {
                        background: #fff !important;
                    }
                </style>

                <button type="button"
                        @click="isConfirming ? $wire.clearConversation() : isConfirming = true"
                        @click.outside="isConfirming = false"
                        :style="{
                                'border': isConfirming ? '1px solid #EF4444' : '1px solid #E5E7EB'
                            }"
                        style="padding: 0.5rem; padding-right: 0.8rem; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; border-radius: 0.3rem; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); outline: none; cursor: pointer; transition: background-color 0.2s, color 0.2s;">
                    <!-- Delete Icon -->
                    <x-doctalk::icons.delete style="flex-shrink: 0; width: 1rem; height: 1rem;" width="18" height="18"/>
                    <!-- Button Text -->
                    <span x-text="isConfirming ? 'Confirm?' : 'Clear'"></span>
                </button>
            </div>

            <!-- Export Dropdown -->
            <div x-data="{ open: false }" style="position: relative;">
                <button
                    @click="open = !open"
                    type="button"
                    :style="{
                            'background-color': open ? '#F9FAFB' : '#f0f0f0',
                            'color': '#1F2937',
                            'border': '1px solid #E5E7EB'
                        }"
                    style="padding: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; border-radius: 0.3rem; background-color: #f0f0f0; color: #1F2937; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); cursor: pointer; transition: background-color 0.2s, color 0.2s; outline: none;">
                    <!-- Export Icon -->
                    <x-doctalk::icons.export style="flex-shrink: 0; width: 1rem; height: 1rem;"/>
                    Export
                    <!-- Dropdown Arrow -->
                    <svg
                        :style="{
                                'transform': open ? 'rotate(180deg)' : 'rotate(0deg)',
                                'transition': 'transform 0.3s'
                            }"
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        style="width: 1rem; height: 1rem; transition: transform 0.3s;">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    x-cloak
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    style="position: absolute; right: 0; top: 100%; margin-top: 0.25rem; min-width: 7rem; background-color: #f0f0f0; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; gap: 0.125rem; z-index: 1001;">
                    <div>
                        <!-- HTML Export Option -->
                        <a
                            @click.prevent="$wire.export('html')"
                            href="#"
                            class="export-option"
                            style="display: flex; align-items: center; gap: 0.875rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #1F2937; text-decoration: none; cursor: pointer; transition: background-color 0.2s;">
                            <!-- Code Icon -->
                            <x-doctalk::icons.code style="width: 1rem; height: 1rem;"/>
                            HTML
                        </a>
                        <!-- TEXT Export Option -->
                        <a
                            @click.prevent="$wire.export('txt')"
                            href="#"
                            class="export-option"
                            style="display: flex; align-items: center; gap: 0.875rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #1F2937; text-decoration: none; cursor: pointer; transition: background-color 0.2s;">
                            <!-- Text Icon -->
                            <x-doctalk::icons.text style="width: 1rem; height: 1rem;"/>
                            TEXT
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="chat-messages" id="chat-messages">

        @foreach($this->messages as $message)

            <div class="message {{$message->ai ? 'received' : 'sent'}}" x-data="{
                copied: false,
                copy () {
                    const answer = this.$refs.content.querySelector('answer');
                    navigator.clipboard.writeText(answer.innerText);
                    this.copied = true;

                    setTimeout(() => {
                        this.copied = false;
                    }, 1000);
                }
              }">

                <div
                    {!! $loop->last && $message->ai ? 'wire:stream="liveUpdate"' : '' !!}
                    class="message-content prose"
                    x-ref="content">

                    @if($message->ai)
                        <bdi>{!! $message->content !!}</bdi>
                    @else
                        <bdi>{!! nl2br(e($message->content)) !!}</bdi>
                    @endif
                </div>

                <div class="action-buttons">
                    <div class="tooltip-wrapper tooltip t-top"
                         @click="copy"
                         data-tooltip-text="Copy">
                        <x-doctalk::icons.copy/>
                        <span
                            style="margin-left:5px;"
                            x-text="typeof(copied) !== 'undefined' && copied ? 'Copied' : ''">
                        </span>
                    </div>

                    @if ($loop->last && $message->ai)
                        <div class="tooltip-wrapper tooltip t-top"
                             style="margin-right:5px;"
                             wire:click="regenerate({{$message->id}})"
                             data-tooltip-text="Regenerate">
                            <x-doctalk::icons.refresh color="#666"/>
                        </div>
                    @endif

                    <div class="tooltip-wrapper tooltip t-top pointer inline-block"
                         data-tooltip-text="Delete"
                         style="margin-top:4px;">

                        <x-doctalk::confirm-dialog call="delete({{$message->id}})">
                            <x-doctalk::icons.delete/>
                        </x-doctalk::confirm-dialog>
                    </div>
                </div>
            </div>

        @endforeach

    </div>
</div>

<div x-data="{ lastQuery: '' }">

    @auth
        Welcome! {{auth()->name ?? auth()->first_name ?? auth()->email ?? ''}}
    @else
        @section('topbar')
            Welcome! Guest
        @endsection
    @endauth

    <livewire:doctalk.sidebar :conversation="$conversation"/>

    <x-doctalk::goodtogo/>

    <div>
        @if ($conversation && $conversation->exists && $conversation->messages->count() > 2)
            <livewire:doctalk.chatlist :conversation="$conversation" lazy="on-load"/>
        @else
            <livewire:doctalk.chatlist :conversation="$conversation"/>
        @endif

        <div x-ref="scrollPoint" id="scrollPoint" wire:ignore></div>
    </div>

    <!-- Chat Input -->
    <div class="chat-input" id="chat-input"
         x-data="{
                lastQuery: '',
                handleKeyDown(event) {
                    if (event.key === 'ArrowUp' && this.$refs.chatInput.value === '') {
                        event.preventDefault();
                        if (this.lastQuery) {
                            this.$refs.chatInput.value = this.lastQuery;
                            $wire.$set('query', this.lastQuery);
                            this.$nextTick(() => {
                                this.$refs.chatInput.selectionStart = this.$refs.chatInput.selectionEnd = this.$refs.chatInput.value.length;
                                this.$refs.chatInput.focus();
                            });
                        }
                    }
                },
                saveLastQuery() {
                    this.lastQuery = this.$refs.chatInput.value;
                },
                sendMessage() {

                    this.saveLastQuery();

                    lastQuery = this.$refs.chatInput.value;

                    const scrollPoint = document.getElementById('scrollPoint');

                    if (this.$refs.chatInput.value.trim()) {
                        @if (isset($conversation) && $conversation->exists && config('doctalk.animated_message'))
                        const containerDiv = document.getElementById('chat-messages');
                        const flyElement = document.getElementById('flyElement');
                        const flyText = document.getElementById('flyText');
                        const targetElement = document.createElement('div');

                        flyText.textContent = this.$refs.chatInput.value;

                        targetElement.id = 'flyTarget';
                        targetElement.classList.add('message', 'sent', 'invisible');
                        targetElement.textContent = this.$refs.chatInput.value;
                        targetElement.style.marginBottom = '100px';
                        containerDiv.appendChild(targetElement);

                        scrollPoint.scrollIntoView({behavior: 'smooth'});

                        this.$refs.chatInput.value = '';

                        const flyRect = flyElement.getBoundingClientRect();
                        const targetRect = targetElement.getBoundingClientRect();

                        const minusWidth = flyRect.width - targetRect.width;
                        const translateX = targetRect.left - 0 - flyRect.left - minusWidth;
                        const translateY = targetRect.top - flyRect.top - 15;

                        flyElement.classList.add('visible');
                        flyElement.style.transition = 'transform 0.1s ease-in-out';
                        flyElement.style.transform = 'translateY(-50px) rotate(25deg)';

                        flyElement.addEventListener('transitionend', function moveToTarget() {
                            flyElement.removeEventListener('transitionend', moveToTarget);

                            flyElement.style.transition = 'transform 0.8s ease-in-out';
                            flyElement.style.transform = `translate(${translateX}px, ${translateY + 50}px)`;

                            flyElement.addEventListener('transitionend', function saveMessage() {
                                flyElement.removeEventListener('transitionend', saveMessage);
                                $wire.save();

                                $wire.on('getAIResponse', () => {
                                    targetElement.remove();
                                    flyElement.style.transition = 'none';
                                    flyElement.style.transform = 'none';
                                    flyElement.classList.remove('visible');
                                    flyElement.classList.add('invisible');
                                });
                            });
                        });
                        @else
                            scrollPoint.scrollIntoView({behavior: 'smooth'});
                            $wire.save();
                        @endif
                    }
                }
            }"

         x-init="

            $wire.on('getAIResponse', () => {
                $refs.chatInput.disabled = true;
                $refs.sendButton.disabled = true;
            });

            $wire.on('focusInput', () => {
                $refs.chatInput.disabled = false;
                $refs.sendButton.disabled = false;
            });
        ">

        <form
            id="chatForm"
            @submit.prevent="sendMessage"
            x-on:message-submitted.window="$refs.chatInput.focus();">

            @if (config('doctalk.allow_user_upload'))
                <div class="tooltip-wrapper tooltip t-top" data-tooltip-text="Attach PDFs">
                    <button type="button" class="attach-button" @click="$dispatch('open-dialog', { size: 'medium' })">
                        <x-doctalk::icons.attach/>
                    </button>
                </div>
            @endif

            <input
                id="chatQuery"
                wire:model="query"
                x-ref="chatInput"
                type="text"
                autocomplete="off"
                autofocus
                wire:loading.attr="disabled"
                @keydown="handleKeyDown"
                dir="auto"
                {{!Package\DocTalk\Services\LLMUtilities::goodToGo() ? 'disabled' : ''}}
                placeholder="Type your message...">

            @if(Package\DocTalk\Services\LLMUtilities::goodToGo())
                <div class="tooltip-wrapper tooltip t-top" data-tooltip-text="Send Message">
                    <button type="submit"
                            class="send-button"
                            x-ref="sendButton"
                            :disabled="!$wire.query.trim()"
                            wire:loading.attr="disabled">
                        <x-doctalk::icons.submit/>
                    </button>
                </div>
            @endif
        </form>
    </div>

    <div id="flyElement" class="message invisible"
         style="
         background-color: #e5fcd4;
         float: left;
         position: absolute;
         text-align: left;
         bottom: 0;
         border-bottom-right-radius: 4px;
         padding: 15px;
         margin-bottom: 0;
         margin-left: 80px;">

        <span id="flyText"></span>

        <div class="action-buttons">

            <div class="tooltip-wrapper tooltip t-top" style="margin-right: 5px;"
                 data-tooltip-text="Copy">
                <x-doctalk::icons.copy/>
            </div>

            <div class="tooltip-wrapper tooltip t-top pointer inline-block"
                 data-tooltip-text="Delete"
                 style="margin-top:4px;">

                <x-doctalk::confirm-dialog call="delete(0)">
                    <x-doctalk::icons.delete/>
                </x-doctalk::confirm-dialog>
            </div>
        </div>
    </div>

    <x-doctalk::dialog>
        <x-slot name="title">Add Documents</x-slot>

        <x-doctalk::errors/>

        <div class="flex alignCenter text-center justifyCenter bold mb-4">
            <small class="blue" wire:stream="liveUpdateFiles"></small>
        </div>

        <div
            class="full-width"
            x-data="{ uploading: false, progress: 0 }"
            x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-cancel="uploading = false"
            x-on:livewire-upload-error="uploading = false"
            x-on:livewire-upload-progress="progress = $event.detail.progress"
            x-on:files-uploaded.window="
                uploading = false;
                progress = 0;
                $refs.fileInput.value = null;
                ">
            <input
                type="file"
                wire:model="files"
                x-ref="fileInput"
                class="custom-file-input"
                id="fileInput"
                multiple
            >
            <br>
            <small>
                Max Size:
                <strong>{{Package\DocTalk\Services\LLMUtilities::sizeInMB(config('doctalk.max_files_upload_size'))}}</strong>
            </small>

            <!-- Progress Bar -->
            <div x-show="uploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
        </div>

        <x-slot name="button">
            <button class="btn btn-blue" wire:click="saveDocs" wire:loading.attr="disabled">
                <x-doctalk::icons.ok width="20" height="20"/>
                <span style="margin-left: 5px;">Save Documents</span>
            </button>
        </x-slot>
    </x-doctalk::dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Livewire.on('focusInput', () => {
                document.getElementById('chatQuery').focus();
            });
        });
    </script>

    <script wire:ignore>
        function setupSuggestedLinks() {
            // Function to decode escaped HTML entities
            function decodeHTMLEntities(text) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, "text/html");
                return doc.documentElement.textContent;
            }

            // Function to convert both escaped and non-escaped related_question tags to links
            function convertRelatedQuestionsToLinks() {
                document.querySelectorAll('.message-content li').forEach(li => {
                    // First, handle escaped related_question tags
                    if (li.innerHTML.includes('&lt;related_question&gt;')) {
                        const decodedHTML = decodeHTMLEntities(li.innerHTML);

                        // Replace related_question tags in the decoded HTML
                        // Update the li element with the new HTML (converted links)
                        li.innerHTML = decodedHTML.replace(/&lt;related_question&gt;/g, '<a class="ai-suggested-answer pointer block" href="#">')
                            .replace(/&lt;\/related_question&gt;/g, '</a>');
                    }

                    // Then handle non-escaped related_question tags
                    if (li.innerHTML.includes('<related_question>')) {
                        // Replace non-escaped related_question tags directly
                        // Update the li element with the new HTML (converted links)
                        li.innerHTML = li.innerHTML.replace(/<related_question>/g, '<a class="ai-suggested-answer block pointer" href="#">')
                            .replace(/<\/related_question>/g, '</a>');
                    }
                });
            }

            function attachLinkEventListeners() {
                document.querySelectorAll('.ai-suggested-answer').forEach(link => {
                    link.removeEventListener('click', handleLinkClick); // Remove existing listener to avoid duplicates
                    link.addEventListener('click', handleLinkClick);
                });
            }

            function handleLinkClick(e) {
                e.preventDefault();
                Livewire.dispatch('suggestedAnswer', [e.target.textContent]);
            }

            // Convert related_question elements (escaped or not) to links initially
            convertRelatedQuestionsToLinks();
            // Attach initial event listeners to the links
            attachLinkEventListeners();

            // MutationObserver to detect changes in the DOM
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Re-run the conversion and listener attachment when new nodes are added
                        convertRelatedQuestionsToLinks();
                        attachLinkEventListeners();
                    }
                }
            });

            // Start observing the document body for changes
            observer.observe(document.body, {childList: true, subtree: true});
        }

        setupSuggestedLinks();
    </script>

</div>

<div>

    <x-doctalk::admin.sidebarlinks/>

    <div class="page">

        <button
            @click="$dispatch('open-dialog', { size: 'medium' })"
            class="btn btn-green">

            <x-doctalk::icons.plus width="18" height="18"/>
            <span style="margin-bottom:1px;">Add Documents</span>
        </button>

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

        <div class="relative my-4" style="background: transparent;">
            <div style="top:13px; left:10px;" class="absolute">
                <x-doctalk::icons.search/>
            </div>
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.500ms="searchQuery"
                       class="full-width"
                       style="background: #eee; outline: none; border:none; padding: 15px 40px; border-radius: 5px;"
                       placeholder="Search content..."/>
            </div>
        </div>

        <table class="my-4">
            <thead>
            <tr>
                <th>Metadata</th>
                <th class="text-center" wire:click.prevent="sortBy('llm')">
                     <span class="tooltip-wrapper tooltip t-top pointer inline-block" data-tooltip-text="Sort">
                        LLM
                    </span>
                </th>
                <th>Content</th>
                <th class="text-center" wire:click.prevent="sortBy('id')">
                     <span class="tooltip-wrapper tooltip t-top pointer inline-block" data-tooltip-text="Sort">
                        Date
                    </span>
                </th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>

            @foreach($this->documents as $document)
                <tr>
                    <td class="text-left">{{$document->metadata}}</td>
                    <td class="text-center">{{$document->llm}}</td>
                    <td x-data="{open:false}" class="relative">
                        <div class="tooltip-wrapper tooltip t-top" @click="open = !open"
                             data-tooltip-text="click to view">
                            <span class="pointer">
                                {{Str::limit($document->content, 50)}}
                            </span>
                        </div>
                        <div x-show="open" x-cloak @click="open = false" class="absolute" style="z-index: 1000">
                            <div class="pointer"
                                 style="background: #f3f4f6; color:#444; padding: 15px; border-radius: 10px; border:1px solid #999;">
                                {{$document->content}}
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{$document->created_at}}</td>
                    <td class="text-center">
                        <span class="tooltip-wrapper tooltip t-top pointer inline-block" data-tooltip-text="Delete">
                            <x-doctalk::confirm-dialog call="delete({{$document->id}})">
                                <x-doctalk::icons.delete/>
                            </x-doctalk::confirm-dialog>
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="my-2 justifyCenter flex">
            {{ $this->documents->links('doctalk::components.pagination') }}
        </div>

    </div>

</div>

<div>
    <div class="sidebar" id="sidebar" x-data="{ openDropdown: null }">

        <div class="alignCenter justifyCenter flex" style="padding: 10px 0; margin: 0 auto;">
            <a href="{{route('doctalk.chat')}}" wire:navigate class="btn btn-green justifyCenter"
               style="font-size: 13px; text-transform: uppercase; padding: 9.5px 0; width: 92%;">
                <x-doctalk::icons.plus width="18" height="18"/>
                New Conversation
            </a>
        </div>

        <div class="relative">
            <div style="top:13px; left:10px;" class="absolute">
                <x-doctalk::icons.search/>
            </div>
            <div>
                <input type="text" wire:model.live.debounce.500ms="searchQuery"
                       class="full-width"
                       style="background: #e3e4e4; outline: none; border:none; padding: 12px 40px;"
                       placeholder="Search Conversations..."/>
            </div>
        </div>

        <ul style="margin-top: 2px;">
            @foreach($this->conversations as $conversation)

                <li x-data="{
                        editable: false,
                        hover: false,
                        hoverItem: false,
                        startEdit() {
                            this.editable = true;
                            this.$nextTick(() => this.$refs.titleEditable.focus());
                        },
                        stopEdit() {
                            if (this.editable) {
                                this.$wire.rename({{$conversation->id}}, this.$refs.titleEditable.innerText);
                                this.editable = false;
                            }
                        },
                        handleKeyDown(event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                this.stopEdit();
                            }
                        },
                        toggleDropdown() {
                            this.openDropdown = (this.openDropdown === {{$conversation->id}}) ? null : {{$conversation->id}};
                        }
                    }"
                    x-cloak
                    class="full-width {{$conversation->id == $this->conversation->id ? 'active' : ''}}"
                    wire:key="conv-{{$conversation->id}}"
                    style="position: relative;"
                    x-bind:style="editable ? 'cursor: normal;': 'cursor: pointer;'"
                    x-on:mouseenter="hover = true"
                    x-on:mouseleave="hover = false"
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    >

                    <div style="display: flex; justify-content: space-between;">
                        <div style="display: flex; align-items: center;"
                             x-bind:style="editable ? 'width:99%': 'width: 80%;'">
                            <a wire:navigate
                               x-show="!editable"
                               href="{{route('doctalk.chat', $conversation->id)}}"
                               style="text-decoration: none; display: block; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">

                                {{ ucwords($conversation->name) }}
                            </a>

                            <div x-show="editable"
                                 x-ref="titleEditable"
                                 x-on:blur="stopEdit"
                                 x-on:keydown="handleKeyDown"
                                 contenteditable="true"
                                 x-bind:style="{
                                    display: 'flex',
                                    alignItems: 'center',
                                    width: '100%',
                                    padding: '0.5rem',
                                    whiteSpace: 'nowrap',
                                    fontSize: '0.875rem',
                                    color: '#4a5568',
                                    border: '1px solid #cbd5e0',
                                    backgroundColor: '#fefcbf',
                                    outline: 'none'
                                 }">
                                {{ ucwords($conversation->name) }}
                            </div>
                        </div>

                        @if($conversation->favorite)
                            <div x-show="!editable" x-bind:style="{
                                    marginLeft: 'auto',
                                    cursor: 'pointer',
                                    paddingRight: '0.6rem',
                                    paddingTop: '0.8rem',
                                    }">
                                <button style="background: transparent; border: none; outline: none;">
                                    <x-doctalk::icons.pin style="display: inline-block;"/>
                                </button>
                            </div>
                        @endif

                        <div
                            style="display: flex; justify-content: flex-end; align-items: center;"
                            x-show="!editable">

                            <div
                                x-on:click.prevent.stop="toggleDropdown()"
                                x-on:mouseenter="hover = true"
                                x-on:mouseleave="hover = false"
                                x-bind:style="{
                                    marginLeft: 'auto',
                                    cursor: 'pointer',
                                    display: hover ? 'inline-block' : 'none',
                                    paddingRight: '0.6rem',
                                    paddingTop: '0.8rem',
                                    }">
                                <button style="background: transparent; border: none; outline: none;">
                                    <x-doctalk::icons.dots style="display: inline-block;"/>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="openDropdown === {{$conversation->id}}"
                         x-on:click.away="openDropdown = null"
                         x-bind:style="{
                            position: 'absolute',
                            right: '5px',
                            minWidth: '100px',
                            backgroundColor: '#ffffff',
                            border: '1px solid #e2e8f0',
                            fontSize: '0.6rem',
                            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                            zIndex: '1001',
                            display: openDropdown === {{$conversation->id}} ? 'block' : 'none'
                         }"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li>
                                <a href="#"
                                   wire:click.prevent="toggleFavorite({{$conversation->id}}); openDropdown = null;"
                                   x-on:mouseenter="hoverItem = true"
                                   x-on:mouseleave="hoverItem = false"
                                   x-bind:style="{
                                        display: 'block',
                                        width: '100%',
                                        padding: '0.3rem 0.7rem',
                                        color: '#4a5568',
                                        textDecoration: 'none',
                                        cursor: 'pointer',
                                        transition: 'background-color 0.2s'
                                        }">

                                    <div style="display: flex; justify-content: flex-start; align-items: center; font-weight: normal;">
                                        <div style="margin-top: 5px; padding-right: 5px;">
                                            <x-doctalk::icons.pin width="16" height="16"/>
                                        </div>

                                        <div>
                                            {{$conversation->favorite ? 'Un-Pin' : 'Pin'}}
                                        </div>
                                    </div>

                                </a>
                            </li>
                            <li>
                                <a href="#"
                                   x-on:click.prevent="startEdit(); openDropdown = null;"
                                   x-on:mouseenter="hoverItem = true"
                                   x-on:mouseleave="hoverItem = false"
                                   x-bind:style="{
                                        display: 'block',
                                        width: '100%',
                                        padding: '0.3rem 0.5rem',
                                        color: '#4a5568',
                                        textDecoration: 'none',
                                        cursor: 'pointer',
                                        transition: 'background-color 0.2s'
                                        }">

                                    <div style="display: flex; justify-content: flex-start; align-items: center; font-weight: normal;">
                                        <div style="margin-top: 5px; padding-right: 7px;">
                                            <x-doctalk::icons.edit width="16" height="16"/>
                                        </div>

                                        <div>
                                            Rename
                                        </div>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <x-doctalk::confirm-dialog call="delete({{$conversation->id}})"
                                                           x-on:mouseenter="hoverItem = true"
                                                           x-on:mouseleave="hoverItem = false"
                                                           x-bind:style="{
                                                            padding: '0.3rem 0.5rem',
                                                            textAlign: 'left',
                                                            display: 'block',
                                                            cursor: 'pointer',
                                                            transition: 'background-color 0.2s',
                                                            width: '100%'
                                                            }">

                                    <div style="display: flex; justify-content: flex-start; align-items: center ">
                                        <div style="margin-top: 5px; padding-right: 7px;">
                                            <x-doctalk::icons.delete width="16" height="16"/>
                                        </div>

                                        <div>
                                            Delete
                                        </div>
                                    </div>

                                </x-doctalk::confirm-dialog>
                            </li>
                        </ul>
                    </div>
                </li>

            @endforeach
        </ul>
    </div>
</div>

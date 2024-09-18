<style>
    .confirm-dialog * {
        transition: none !important;
        animation: none !important;
    }
</style>

<div
    class="confirm-dialog"
    x-data="{
        open: false,
        closeOtherDialogs() {
            document.querySelectorAll('.modal-overlay').forEach(el => {
                if (el.style.display === 'flex' && el !== $refs.overlay) {
                    el.style.display = 'none';
                }
            });
        },
        openDialog() {
            this.closeOtherDialogs();
            $refs.overlay.style.display = 'flex';
            this.open = true;
        },
        closeDialog() {
            $refs.overlay.style.display = 'none';
            this.open = false;
        }
    }"
    style="display: inline;"
>
    <!-- Trigger Button -->
    <button
        style="background: none; border: none; cursor: pointer;"
        @click="openDialog"
        {{ $attributes->merge(['style' => '']) }}
    >
        {{ $slot }}
    </button>

    <!-- Overlay and Confirmation Dialog -->
    <div
        x-ref="overlay"
        class="modal-overlay"
        @click.self="closeDialog"
        style="
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        "
    >
        <div
            role="dialog"
            aria-modal="true"
            style="

                background-color: #fff;
                border-radius: 0.5rem;
                padding: 1rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                            0 4px 6px -4px rgba(0, 0, 0, 0.1);
                max-width: 90%;
                width: 400px;
            "
        >
            <div class="dialog-content">
                <div
                    style="
                        margin-bottom: 1rem;
                        font-weight: 600;
                        font-size: 1.125rem;
                        color: #6b7280;
                    "
                >
                    {{$text ?? 'Are you sure you want to delete?'}}
                </div>

                <div
                    style="
                        display: flex;
                        justify-content: flex-end;
                        gap: 1rem;
                    "
                >
                    <button
                        @click="closeDialog"
                        style="
                            padding: 0.5rem 1rem;
                            background-color: #e5e7eb;
                            color: #4b5563;
                            border: none;
                            border-radius: 0.375rem;
                            cursor: pointer;
                            font-size: 1rem;
                        "
                        onmouseover="this.style.backgroundColor='#d1d5db';"
                        onmouseout="this.style.backgroundColor='#e5e7eb';"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="{{ $call }}"
                        @click="closeDialog"
                        style="
                            padding: 0.5rem 1rem;
                            background-color: #dc2626;
                            color: #ffffff;
                            border: none;
                            border-radius: 0.375rem;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            font-size: 1rem;
                        "
                        onmouseover="this.style.backgroundColor='#b91c1c';"
                        onmouseout="this.style.backgroundColor='#dc2626';"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

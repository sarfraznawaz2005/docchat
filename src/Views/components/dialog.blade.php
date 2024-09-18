<style>
    .dialog-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }

    .dialog-overlay[x-cloak] {
        display: flex;
    }

    .dialog-content {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        position: relative;
        background-color: #fff;
        border-radius: 8px;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        animation: dialog-appear 0.1s ease-out;
    }

    .dialog-small {
        max-width: 400px;
    }

    .dialog-medium {
        max-width: 600px;
    }

    .dialog-large {
        max-width: 800px;
    }

    .dialog-close-button {
        background: none;
        border: none;
        font-size: 24px;
        color: #aaa;
        cursor: pointer;
        padding-top: 2px;
    }

    .dialog-close-button:hover {
        color: #000;
    }

    .dialog-body {
        font-size: 16px;
        color: #555;
        padding: 20px;
    }

    .dialog-footer {
        text-align: right;
        border-top: 1px solid #eee;
        padding: 15px 20px;
    }

    @keyframes dialog-appear {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 600px) {
        .dialog-content {
            max-width: 90%;
            margin: 0 10px;
        }
    }

    .dialog-header {
        padding: 12px 15px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        border-bottom: 1px solid #eee;
        background: #f9f9f9;
    }

    .dialog-title {
        font-size: 16px;
        font-weight: 600;
        color: #555;
    }

</style>

<div
    wire:ignore.self
    x-data="{
        open: false,
        size: 'medium',
        openDialog(size = 'medium') {
            this.size = size;
            this.open = true;
        },
        closeDialog() {
            this.open = false;
        }
    }"
    x-on:open-dialog.window="openDialog($event.detail.size)"
    x-on:close-dialog.window="closeDialog()"
    @keydown.escape.window="closeDialog()"
    x-show="open"
    x-cloak
    class="dialog-overlay"
>
    <div
        :class="{
            'dialog-content dialog-small': size === 'small',
            'dialog-content dialog-medium': size === 'medium',
            'dialog-content dialog-large': size === 'large'
        }"
        @click.stop
    >
        <!-- Close button on top right -->

        <div class="dialog-header flex alignCenter {{isset($title)? 'justifyBetween' : 'justifyEnd'}} ">
            @if (isset($title))
                <div class="dialog-title">{!! $title !!}</div>
            @endif

            <button @click="closeDialog()" class="dialog-close-button" style="margin-top: -5px;">
                &times;
            </button>
        </div>


        <!-- Dialog Content -->
        <div class="dialog-body">
            {!! $slot !!}
        </div>

        <!-- Footer -->
        <div class="dialog-footer flex alignCenter justifyEnd gap-x-2">
            <button @click="closeDialog()" class="btn btn-gray" style="border: 1px solid #a0aec0;">
                Close
            </button>

            {!! $button ?? '' !!}
        </div>
    </div>
</div>

<div
    wire:ignore
    x-data="flashMessageComponent()"
    x-init="init()"
    x-show="visible"
    :class="messageClass"
    x-transition.opacity.duration.500ms
    x-cloak
    style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"
>
    <span x-show="visible" class="message" x-text="message" style="padding: 15px 20px;"></span>
</div>

<script>
    function flashMessageComponent() {
        return {
            visible: false,
            message: '',
            type: 'info',
            timeout: null,
            init() {
                Livewire.on('flashMessage', data => {
                    data = data[0];
                    this.show(data.message, data.type ?? 'info', data.timeout ?? 5000);
                });
            },
            show(message, type, timeout) {
                this.visible = true;
                this.message = message;
                this.type = type;

                if (this.timeout) {
                    clearTimeout(this.timeout);
                }

                this.timeout = setTimeout(() => {
                    this.hide();
                }, timeout);
            },
            hide() {
                this.visible = false;
                this.message = '';
                this.type = 'info';
                clearTimeout(this.timeout);
            },
            get messageClass() {
                return {
                    'flash-message': true,
                    [this.type]: true,
                };
            },
        };
    }
</script>

<style>

    .flash-message {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        background-color: #3490dc;
    }

    .flash-message .message {
        color: #fff;
    }

    .flash-message.info {
        background-color: #3490dc;
        padding: 10px 0;
    }

    .flash-message.success {
        background-color: #38c172;
        padding: 10px 0;
    }

    .flash-message.warning {
        background-color: #c3b537;
        padding: 10px 0;
    }

    .flash-message.error {
        background-color: #e3342f;
        padding: 10px 0;
    }
</style>

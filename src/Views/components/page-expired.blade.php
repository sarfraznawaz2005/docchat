<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('expiredNotification', () => ({
            expired: false,

            init() {
                Livewire.hook('request', ({fail}) => {
                    fail(({status, preventDefault}) => {
                        if (status === 419) {
                            this.expired = true;
                            preventDefault();
                        }
                    })
                })
            }
        }))
    });
</script>
<div
    wire:ignore
    x-data="expiredNotification"
>
    <div
        x-cloak
        style="
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: fixed;
            z-index: 1001;
            top:30%;
            left: 40%;
            background-color: transparent;
            align-items: center;
            justify-content: center;
        "
        x-show="expired"
    >

        <div
            style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(107, 114, 128, 0.75); /* Equivalent to bg-gray-500 opacity-75 */
            "
            x-show="expired"
        ></div>

        <div
            style="
                position: relative;
                background-color: #f3f4f6;
                border-radius: 0.5rem;
                padding-left: 1rem;
                padding-right: 1rem;
                padding-bottom: 1rem;
                overflow: hidden;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                            0 4px 6px -4px rgba(0, 0, 0, 0.1);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            "
            x-show="expired"
            x-on:click.outside="expired = false"
            x-on:keydown.window.escape="expired = false"
        >

            <div
                style="
                    width: 100%;
                    text-align: center;
                "
                x-show="expired"
            >
                <p
                    style="
                        font-weight: bold;
                        color: #dc2626;
                        margin-bottom: 1rem;
                        font-size: 1rem;
                    "
                >
                    This Page has Expired
                </p>
                <p
                    style="
                        margin-bottom: 1rem;
                        font-size: 1rem;
                    "
                >
                    Click the button below to refresh the page.
                </p>

                <div
                    style="
                        display: flex;
                        justify-content: center;
                    "
                >
                    <button
                        x-on:click="window.location.reload()"
                        class="btn btn-blue"
                    >
                        <x-doctalk::icons.refresh width="16" height="16" /> <span style="margin-left: 5px;">Refresh Page</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

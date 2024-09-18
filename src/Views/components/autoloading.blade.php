<div
    wire:ignore
    x-data="{ loading: true, error: false }"
    x-show="loading || $store.loading || error"
    x-init="$nextTick(() => { loading = false; })"
    style="
        position: fixed;
        top: 40%;
        left: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10001;
    "
>
    <!-- Backdrop -->
    <div
        style="
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: black;
            opacity: 0;
            z-index: 70;
        "
    ></div>

    <!-- Spinner Container -->
    <div
        style="
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            z-index: 1000;
        "
        x-show="!error"
    >
        <!-- Spinner SVG -->
        <svg
            style="
                width: 3rem;
                height: 3rem;
                color: #2563eb;
                animation: spin 1s linear infinite;
            "
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="2"
            ></circle>
            <path
                style="opacity: 0.75;"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            ></path>
        </svg>
    </div>
</div>

<!-- Inline CSS for Spinner Animation -->
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('loading', false);
        Alpine.store('error', false);
    });

    if (window.Livewire) {
        initializeLoading();
    } else {
        document.addEventListener('livewire:init', () => {
            initializeLoading();
        });
    }

    function initializeLoading() {
        Livewire.hook('request', ({component, options, payload, respond, succeed, fail}) => {

            // Define the methods that should ignore the loading spinner
            const ignoreMethodsForLoading = ['userMessage', 'loadBots', 'loadModels'];

            // Loop over the ignoreMethodsForLoading array and skip execution if payload contains any of the methods
            for (let i = 0; i < ignoreMethodsForLoading.length; i++) {
                if (payload.includes(ignoreMethodsForLoading[i])) {
                    return;
                }
            }

            // Otherwise, show the loading spinner
            Alpine.store('loading', true);
            Alpine.store('error', false);

            succeed(({status, json}) => {
                Alpine.store('loading', false);
            });

            fail(({status, body}) => {
                Alpine.store('loading', false);
                Alpine.store('error', true);
                setTimeout(() => {
                    Alpine.store('error', false);
                }, 5000); // Hide error message after 5 seconds
            });
        });

        Livewire.hook('element.updated', (el, component) => {
            Alpine.store('loading', false);
        });

        document.addEventListener('livewire:navigate:start', () => {
            Alpine.store('loading', true);
            Alpine.store('error', false);
        });

        document.addEventListener('livewire:navigate:end', () => {
            Alpine.store('loading', false);
        });
    }
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loading', () => ({
            show: false,

            init() {
                Livewire.on('showLoading', () => this.show = true);
                Livewire.on('hideLoading', () => this.show = false);
            }
        }))
    });
</script>

<div
    x-data="loading"
    x-show="show"
    wire:ignore
    x-cloak
    class="loading-container">
    <div class="loading-overlay"></div>

    <span
        class="loading-icon"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="loading-svg"
            fill="currentColor"
            viewBox="0 0 16 16">
            <path
                d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"
            />
        </svg>
    </span>
</div>

<style>
    .loading-container {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .loading-overlay {
        position: fixed;
        inset: 0;
        background-color: transparent;
        opacity: 0.05;
        z-index: 70;
    }

    .loading-icon {
        margin-bottom: 1rem;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        border: 4px solid #f0fdf4;
        background-color: #bbf7d0;
        color: #10b981;
        animation: ping 1s infinite;
    }

    .loading-svg {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
    }

    @keyframes ping {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        75%,
        100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    /* Dark mode adjustments */
    .loading-icon.dark {
        background-color: #065f46;
        border-color: #064e3b;
        color: #f0fdf4;
    }
</style>

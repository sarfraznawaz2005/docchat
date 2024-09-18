<div>
    <div
        wire:ignore
        style="
        position: absolute;
        top: 40%;
        left: 55%;
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

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</div>

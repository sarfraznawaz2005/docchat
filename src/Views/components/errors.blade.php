@if (isset($errors) && $errors->any())
    <div wire:ignore class="alert-box" role="alert" tabindex="-1" aria-labelledby="hs-bordered-red-style-label">
        <div class="alert-content">
            <div class="alert-icon-wrapper">
                <span class="alert-icon">
                    <svg class="alert-icon-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </span>
            </div>

            <div class="alert-message">
                <h3 id="hs-bordered-red-style-label" class="alert-title">
                    Oops!
                </h3>

                <ul class="alert-errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>

    <style>
        .alert-box {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-bottom: 1rem;
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 1rem;
        }

        @media (prefers-color-scheme: dark) {
            .alert-box {
                background-color: rgba(153, 27, 27, 0.3);
            }
        }

        .alert-content {
            display: flex;
        }

        .alert-icon-wrapper {
            flex-shrink: 0;
        }

        .alert-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            border: 4px solid #fee2e2;
            background-color: #fecaca;
            color: #991b1b;
        }

        @media (prefers-color-scheme: dark) {
            .alert-icon {
                border-color: #7f1d1d;
                background-color: #991b1b;
                color: #f87171;
            }
        }

        .alert-icon-svg {
            flex-shrink: 0;
            width: 1rem;
            height: 1rem;
        }

        .alert-message {
            margin-left: 0.75rem;
        }

        .alert-title {
            color: #4b5563;
            font-weight: 600;
        }

        @media (prefers-color-scheme: dark) {
            .alert-title {
                color: #ffffff;
            }
        }

        .alert-errors {
            margin-top: 0.75rem;
            list-style-type: disc;
            list-style-position: inside;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #dc2626;
        }

        @media (prefers-color-scheme: dark) {
            .alert-errors {
                color: #f87171;
            }
        }
    </style>

@endif


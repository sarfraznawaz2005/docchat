<?php

namespace Package\DocTalk\Livewire;

use Livewire\Component;

class Offline extends Component
{
    /** @noinspection ALL */
    public function render(): string
    {
        return <<<blade
            <div
                wire:offline
                style="
                    width: 100%;
                    margin: 1rem 0;
                    position: relative;
                    flex-direction: row;
                    z-index: 1000;
                    justify-content: center;
                    align-items: center;
                    margin-left: auto;
                    margin-right: auto;
                "
            >
                <div
                    style="
                        padding: 0.75rem;
                        font-size: 0.875rem;
                        margin: 0 20px;
                        word-break: break-word;
                        display: flex;
                        align-items: center;
                        border-radius: 0.5rem;
                        background-color: #ffce7d;
                    "
                >
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        "
                    >
                        <svg
                            aria-hidden="true"
                            focusable="false"
                            data-prefix="fas"
                            data-icon="info-circle"
                            style="
                                width: 1rem;
                                height: 1rem;
                                margin-right: 0.5rem;
                                fill: currentColor;
                            "
                            role="img"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512"
                        >
                            <path
                                fill="currentColor"
                                d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"
                            ></path>
                        </svg>
                        <p
                            style="
                                font-weight: bold;
                                font-size: 0.875rem;
                                color: #555;
                                word-break: break-word;
                            "
                        >
                           Whoops, your device has lost connection. The web page you are viewing is offline.
                        </p>
                    </div>
                </div>
            </div>
        blade;
    }
}

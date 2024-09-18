@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

<div>
    <style>
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .pagination-container *, .pagination-container button {
            font-size: 13px !important;
        }

        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            margin: 0;
        }

        .page-item {
            margin: 0 0.25rem;
            font-size: 13px !important;
        }

        .page-item button, .page-item span {
            color: #007bff;
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
            font-size: 13px !important;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            min-width: 50px;
            height: 38px;
            line-height: 1.5;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-item button:hover {
            background-color: #e9ecef;
            font-size: 13px !important;
        }

        .page-item.active span {
            background-color: #007bff;
            color: white;
            font-size: 13px !important;
        }

        .page-item.disabled span {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
            font-size: 13px !important;
        }

        .results-count * {
            font-size: 1rem !important;
            color: #6c757d;
        }

        @media (max-width: 576px) {
            .pagination-container {
                flex-direction: column;
                align-items: center;
                font-size: 13px !important;
            }
            .page-item {
                margin-bottom: 0.5rem;
                font-size: 13px !important;
            }
        }
    </style>

    @if ($paginator->hasPages())
        <nav class="pagination-container">
            <div class="results-count">
                <p class="small text-muted">
                    {!! __('Showing') !!}
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                </p>
            </div>

            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <button type="button" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">Prev</button>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $page }}</button></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <button type="button" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">Next</button>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Next</span>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>

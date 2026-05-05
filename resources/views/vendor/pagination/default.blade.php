@if ($paginator->hasPages())
    <nav class="modern-pagination" aria-label="Pagination Navigation">
        <ul class="pagination-list">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-link-prev" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 12L6 8L10 4"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="pagination-link pagination-link-prev"
                       rel="prev"
                       aria-label="@lang('pagination.previous')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 12L6 8L10 4"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)

                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="pagination-item disabled">
                        <span class="pagination-link pagination-dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active" aria-current="page">
                                <span class="pagination-link pagination-link-number">{{ $page }}</span>
                            </li>
                        @else
                            <li class="pagination-item">
                                <a href="{{ $url }}" class="pagination-link pagination-link-number">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif

            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="pagination-link pagination-link-next"
                       rel="next"
                       aria-label="@lang('pagination.next')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 12L10 8L6 4"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
            @else
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-link-next" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 12L10 8L6 4"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </span>
                </li>
            @endif

        </ul>
    </nav>
@endif

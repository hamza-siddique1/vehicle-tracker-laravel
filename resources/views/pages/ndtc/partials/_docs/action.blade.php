{{-- _doc-item.blade.php --}}
@php
    $isFailed = $variant === 'failed';
@endphp

<div class="doc-item {{ $isFailed ? 'doc-rejected mb-2' : '' }}">
    <div class="doc-icon {{ $isFailed ? 'rejected' : '' }}">
        <i class="fas fa-file-pdf" style="color:{{ $isFailed ? '#d9534f' : '#3f80ea' }}"></i>
    </div>
    <div class="doc-info">
        <div class="doc-name">{{ $doc->file_display_name ?? $doc->document_content }}</div>
        <div class="doc-meta">
            @if($isFailed)
                <span class="badge badge-danger" style="font-size:.65rem">
                    <i class="fas fa-times mr-1"></i>Failed
                </span>
            @else
                <span class="badge badge-success" style="font-size:.65rem">
                    <i class="fas fa-check mr-1"></i>{{ ucfirst(strtolower($doc->status)) }}
                </span>
            @endif

            @if($doc->file_mime_type)
                <span>{{ strtoupper(explode('/', $doc->file_mime_type)[1] ?? $doc->file_mime_type) }}</span>
            @endif
            @if($doc->file_size_bytes)
                <span>{{ round($doc->file_size_bytes / 1024, 1) }} KB</span>
            @endif

            @if(!$isFailed && $doc->is_system_generated)
                <span class="text-muted">System generated</span>
            @elseif($doc->uploaded_at)
                <span>Uploaded {{ $doc->uploaded_at->format('M d, Y') }}</span>
            @endif

            @if(!$isFailed)
                <span class="badge badge-secondary" style="font-size:.6rem">
                    {{ $doc->document_content }}
                </span>
            @endif
        </div>

        @if($isFailed && $doc->upload_error)
            <div class="doc-reason">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
                {{ $doc->upload_error }}
            </div>
        @endif
    </div>

    <div class="doc-actions">
        @if($doc->ndtc_document_id)
            <a href="{{ route('ndtc.orders.documents.view', [$order, $doc]) }}"
               class="btn btn-sm btn-outline-secondary btn-xs" target="_blank">
                <i class="fas fa-eye mr-1"></i>View
            </a>
        @endif

        @if($doc->canBeReplaced() && ($isFailed || !$order->isTerminal()))
            <button class="btn btn-sm {{ $isFailed ? 'btn-danger' : 'btn-outline-primary' }} btn-xs"
                    data-toggle="modal"
                    data-target="#replaceDocModal"
                    data-doc-id="{{ $doc->id }}"
                    data-doc-content="{{ $doc->document_content }}">
                <i class="fas fa-upload {{ $isFailed ? 'mr-1' : '' }}"></i>{{ $isFailed ? 'Replace' : '' }}
            </button>
        @endif

        @if($isFailed || $doc->canBeReplaced())
            <button class="btn btn-sm btn-outline-danger btn-xs"
                    data-toggle="modal"
                    data-target="#deleteDocModal"
                    data-doc-id="{{ $doc->id }}"
                    data-doc-name="{{ $doc->file_display_name ?? $doc->document_content }}">
                <i class="fas fa-trash mr-1"></i>Delete
            </button>
        @endif
    </div>
</div>

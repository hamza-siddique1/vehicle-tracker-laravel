@if($failedDocs->count() > 0)
    <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="fas fa-exclamation-triangle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>{{ $failedDocs->count() }} document(s) require attention.</strong>
            Replace the flagged document(s) before resubmitting, or delete if no longer needed.
        </div>
    </div>

    <div class="section-divider">
        <i class="fas fa-exclamation-circle text-danger"></i> Requires Action
    </div>

    @foreach($failedDocs as $doc)
        @include('pages.ndtc.partials._docs.action', ['doc' => $doc, 'order' => $order, 'variant' => 'failed'])
    @endforeach
@endif

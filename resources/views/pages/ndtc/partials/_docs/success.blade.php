@if($uploadedDocs->count() > 0)
    <div class="section-divider">
        <i class="fas fa-check-circle text-success"></i> Uploaded Documents
    </div>

    @foreach($uploadedDocs as $doc)
        @include('pages.ndtc.partials._docs.action', ['doc' => $doc, 'order' => $order, 'variant' => 'uploaded'])
    @endforeach
@endif

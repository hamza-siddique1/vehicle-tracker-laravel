<div class="pipeline-wrap">
    <div class="pipeline">
        @php
            $steps = [
                1 => 'Created',
                2 => 'Ready for<br>Documents',
                3 => 'Docs<br>Uploaded',
                4 => 'Finalized',
                5 => 'Processing',
                6 => $isCancelled ? 'Cancelled' : 'Rejected',
                7 => 'Approved',
            ];
        @endphp
        @foreach($steps as $step => $label)
            @php
                if ($step === 6) {
                    if ($isRejected)       $cls = 'err';
                    elseif ($isCancelled)  $cls = 'err';
                    elseif ($isApproved)   $cls = 'done';
                    elseif ($rank >= 6)    $cls = 'done';
                    else                   $cls = 'wait';
                } elseif ($step === 7) {
                    $cls = $isApproved ? 'done' : 'wait';
                } elseif ($step === 4) {
                    $cls = $order->ready_to_finalize_at ? 'done' : ($rank === 4 ? 'active' : 'wait');
                } else {
                    if ($rank > $step)      $cls = 'done';
                    elseif ($rank === $step) $cls = 'active';
                    else                    $cls = 'wait';
                }
            @endphp
            <div class="pipe-step {{ $cls }}">
                <div class="pipe-dot">
                    @if($cls === 'done')         <i class="fas fa-check"></i>
                    @elseif($cls === 'err')       <i class="fas fa-times"></i>
                    @elseif($cls === 'active')    <i class="fas fa-circle" style="font-size:.4rem"></i>
                    @else                         –
                    @endif
                </div>
                <div class="pipe-label">{!! $label !!}</div>
            </div>
        @endforeach
    </div>
</div>

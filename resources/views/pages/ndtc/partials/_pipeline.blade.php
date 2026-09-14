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

            // Determine the highest step actually reached, based on real milestones —
            // not just $rank, so nothing can "skip" past an earlier unset step.
            $highestReached = 1;
            if ($order->ready_for_documents_at) $highestReached = 2;
            if ($order->ready_to_finalize_at)   $highestReached = 3;
            if ($order->finalized_at)           $highestReached = 4;
            if ($rank >= 5 || $isRejected || $isCancelled || $isApproved) $highestReached = 5;
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
                } else {
                    if ($highestReached > $step)      $cls = 'done';
                    elseif ($highestReached === $step && !$isRejected && !$isCancelled) $cls = 'active';
                    elseif ($highestReached === $step) $cls = 'done'; // reached but order moved on to rejected/cancelled
                    else $cls = 'wait';
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

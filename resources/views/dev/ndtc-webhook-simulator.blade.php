{{-- resources/views/dev/ndtc-webhook-simulator.blade.php --}}
@extends('layouts.app')

@section('title', 'NDTC Webhook Simulator (Dev Only)')

@section('content')
<div class="alert alert-warning">
    <i class="fas fa-flask mr-1"></i>
    <strong>Development tool.</strong> This page only exists in the local environment and lets you manually fire simulated NDTC webhooks against your own webhook endpoint, signed with your real HMAC secret.
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('dev.ndtc.webhook-simulator.send') }}" id="simulatorForm">
            @csrf
            <div class="form-group">
                <label class="font-weight-bold">Order ID (NDTC ndtc_order_id)</label>
                <input type="text" name="order_id" class="form-control" style="max-width:400px"
                       placeholder="e.g. 6a804dceab30c40578d737c9" required>
            </div>

            <input type="hidden" name="event" id="eventInput">

            <label class="font-weight-bold d-block mb-2">Trigger event:</label>
            <div class="d-flex flex-wrap" style="gap:.5rem">
                @foreach($events as $event)
                    <button type="submit" class="btn btn-outline-primary btn-sm"
                            onclick="document.getElementById('eventInput').value = '{{ $event }}'">
                        {{ $event }}
                    </button>
                @endforeach
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Vehicle Details')

@section('scripts')
    <script>
        var vin = "{{ $vehicle->vin }}";

        Swal.fire({
            title: vin,
            text: "Is the above VIN correct?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10875b',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, its perfect',
            cancelButtonText: 'No'
        }).then((result) => {
            if (!result.isConfirmed) {
                window.history.back();
            }
        })
    </script>

@endsection

@section('content')

    <h1 class="h3 mb-3"> Vehicle Details </h1>

    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="danger">{{ session('error') }}</x-alert>
    @endif
    @if (session('warning'))
        <x-alert type="warning">{{ session('warning') }}</x-alert>
    @endif

    <div class="row">
        <div class="col-12 col-xl-6">
            <div class="card">

                <div class="card-body">
                    <form method="post" action="{{ route('vinocr.update.detail2') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Vehicle ID</label>
                            <input type="text" disabled class="form-control" value="{{ $vehicle->id }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vin Number</label>
                            <input type="text" class="form-control" name="vin" value="{{ $vehicle->vin }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Location</label>
                            <select name="location" class="form-control">
                                <option value="-1">Select </option>
                                <option value="NY - NEWBURGH">NEWBURGH</option>
                                <option value="NJ - PATERSON">PATERSON</option>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Year-Make-Model</label>
                            <input type="text" class="form-control" name="description"
                                value="{{ $vehicle->description }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Created At</label>
                            <input type="text" disabled class="form-control"
                                value="{{ now()->format('Y-m-d h:i:s A') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <select name="status" class="form-control">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @if ($status == 'Intake') selected @endif>
                                        {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Has Keys</label>
                            <select name="keys" class="form-control">
                                <option value="YES">YES</option>
                                <option value="No">NO</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mileage</label>
                            <input type="text" class="form-control" name="mileage">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="note" placeholder="Add Notes" rows="3" spellcheck="false"></textarea>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Add Vehicle</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>

    </div>


@endsection
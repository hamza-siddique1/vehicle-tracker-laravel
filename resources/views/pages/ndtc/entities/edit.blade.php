@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <a href="{{ route('ndtc.entities.index') }}" class="small">&larr; Back</a>
    <h4 class="mb-3">Edit {{ $entity->name }}</h4>

    <form method="POST" action="{{ route('ndtc.entities.update', $entity) }}">
        @csrf @method('PUT')
        <div class="card"><div class="card-body"><div class="row gy-3">
            @foreach ([
                'name' => 'Entity Name', 'address_line_1' => 'Address Line 1', 'address_line_2' => 'Address Line 2',
                'city' => 'City', 'state' => 'State (2 letters)', 'zip' => 'ZIP', 'county' => 'County',
                'phone_number' => 'Phone Number',
            ] as $field => $label)
                <div class="col-md-6 col-lg-4">
                    <label class="form-label small text-muted mb-1">{{ $label }}</label>
                    <input type="text" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror"
                           value="{{ old($field, $entity->$field) }}">
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div></div></div>
        <button class="btn btn-primary mt-3">Save</button>
    </form>
</div>
@endsection

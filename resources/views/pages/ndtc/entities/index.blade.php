@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <h4 class="mb-3">Disposing Entities</h4>
    <div class="card"><div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Name</th><th>Address</th><th>City</th><th>State</th><th>ZIP</th><th>Phone</th><th></th></tr></thead>
            <tbody>
            @foreach ($entities as $e)
                <tr>
                    <td>{{ $e->name }}</td>
                    <td>{{ $e->address_line_1 }}</td>
                    <td>{{ $e->city }}</td>
                    <td>{{ $e->state }}</td>
                    <td>{{ $e->zip }}</td>
                    <td>{{ $e->phone_number }}</td>
                    <td><a href="{{ route('ndtc.entities.edit', $e) }}" class="btn btn-sm btn-primary">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $entities->links() }}</div>
</div>
@endsection

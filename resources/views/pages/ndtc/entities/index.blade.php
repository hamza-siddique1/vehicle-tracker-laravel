@extends('layouts.app')

@section('title', __('Disposing Entities'))

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#entities-table').DataTable();
        });
    </script>
@endsection

@section('content')
    <h1 class="h3 mb-3">{{ __('Disposing Entities') }}</h1>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="entities-table" class="table table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>State</th>
                            <th>ZIP</th>
                            <th>Phone</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($entities as $e)
                            <tr>
                                <td>{{ $e->name }}</td>
                                <td>{{ $e->address_line_1 }}</td>
                                <td>{{ $e->city }}</td>
                                <td>{{ $e->state }}</td>
                                <td>{{ $e->zip }}</td>
                                <td>{{ $e->phone_number }}</td>
                                <td data-sort="{{ $e->created_at?->timestamp }}">{{ $e->created_at?->diffForHumans() }}</td>
                                <td class="table-action">
                                    <a href="{{ route('ndtc.entities.edit', $e) }}" class="btn" style="display: inline">
                                        <i class="fa fa-edit text-info"></i>
                                    </a>

                                    {{-- only a full admin may delete a saved entity --}}
                                    @if (auth()->user()->role === 'admin')
                                        <form method="post" action="{{ route('ndtc.entities.destroy', $e) }}"
                                              onsubmit="return confirmSubmission(this, 'Are you sure you want to delete ' + {{ \Illuminate\Support\Js::from($e->name) }} + '?')"
                                              style="display: inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn text-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

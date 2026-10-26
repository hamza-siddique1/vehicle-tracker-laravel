@extends('layouts.app')

@section('title', __('Users'))

@section('scripts')
    <script>
        {{--function get_query_params() {--}}
        {{--    var urlParams = new URLSearchParams(window.location.search);--}}
        {{--    var query = urlParams.toString();--}}
        {{--    let url = "{{ route('users.export', ':id') }}";--}}
        {{--    url = url.replace(':id', query);--}}
        {{--    document.location.href = url;--}}
        {{--}--}}

        $(document).ready(function () {
            $('#users-table').DataTable();
        });
    </script>
@endsection

@section('content')
    <h1 class="h3 mb-3">{{ __('All Users') }}</h1>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="dt-buttons btn-group flex-wrap">
{{--                        <button class="mb-4 btn btn-secondary buttons-copy buttons-html5"--}}
{{--                                type="button"--}}
{{--                                id="btn_export"--}}
{{--                                value="1"--}}
{{--                                onclick="get_query_params()">--}}


{{--                            <span>Export</span>--}}
{{--                        </button>--}}

                    </div>

                    @if(session('delete'))
                        <x-alert type="danger">{{ session('delete') }}</x-alert>
                    @elseif(session('password_update'))
                        <x-alert type="success">{{ session('password_update') }}</x-alert>
                    @elseif(session('account'))
                        <x-alert type="success">{{ session('account') }}</x-alert>
                    @endif


                    <table id="users-table" class="table table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th>{{ 'ID' }}</th>
                            <th>{{ 'Name' }}</th>
                            <th>{{ 'Email' }}</th>
                            <th>{{ 'Role' }}</th>
                            <th>{{ 'Created at' }}</th>
                            <th>{{ 'Actions' }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>

                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $user->name }}
                                </td>
                                <td>{{  $user->email }}</td>

                                <td>
                                    @if ($user->role == 'admin')
                                        <span class="badge badge-success"> Admin </span>
                                    @elseif($user->role == 'vehicle_manager')
                                        <span class="badge badge-primary">Vehicle Manager</span>
                                    @elseif($user->role == 'yard_manager')
                                        <span class="badge badge-info">Yard Manager</span>
                                    @elseif($user->role == 'ndtc_manager')
                                        <span class="badge badge-secondary">NDTC Manager</span>
                                    @elseif($user->role == 'viewer')
                                        <span class="badge badge-warning">Viewer</span>
                                    @endif
                                </td>


                                <td data-sort="{{ strtotime($user->created_at) }}">{{ $user->created_at->diffForHumans() }}</td>
                                <td class="table-action">




                                    <a href="{{ route('users.edit', $user->id) }}" class="btn" style="display: inline">
                                        <i class="fa fa-edit text-info"></i>
                                    </a>


                                    @if (auth()->user()->id != $user->id)
                                        <form method="post" action="{{ route('users.destroy', $user->id) }}"
                                              onsubmit="return confirmSubmission(this, 'Are you sure you want to delete user ' + '{{ "$user->name"  }}')"
                                              style="display: inline">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn text-danger"
                                                    href="{{ route('users.destroy', $user->id) }}">
                                                <i class="fa fa-trash"></i>

                                            </button>
                                        </form>

                                            @canImpersonate($guard = null)
                                            <a href="{{ route('impersonate', $user->id) }}" class="btn" style="display: inline">
                                                <i class="fa fa-user-cog"></i>
                                            </a>
                                            @endCanImpersonate
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

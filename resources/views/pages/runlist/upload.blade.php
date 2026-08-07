@extends('layouts.app')

@section('title', 'Add File')

@section('scripts')
    <script>
        $('#add').click(function() {
            // alert('sss');
            $('#loader').toggleClass('d-none');
        });
    </script>
@endsection
@section('content')
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="danger">{{ session('error') }}</x-alert>
    @endif
    @if (session('warning'))
        <x-alert type="warning">{{ session('warning') }}</x-alert>
    @endif

    <h1 class="h3 mb-3">Add New CSV Run List </h1>

    <div class="row">

        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('vehicles.runlist') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <div class="mb-3">
                                <label class="form-label w-100">Upload New Run List File.</label>
                                <input type="file" name="csv_file" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" id="add" class="btn btn-lg btn-primary"><i class="align-middle" data-feather="upload"></i> Upload New File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
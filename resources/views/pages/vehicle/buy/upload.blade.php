@extends('layouts.app')

@section('title', 'Add File')

@section('scripts')
    <script>
        $(document).ready(function() {
            // Add click event listener to buttons with class "add-btn"
            $('.add-btn').click(function() {
                $('#loader').toggleClass('d-none');
            });
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


    <h1 class="h3 mb-3">Step 1: Upload Purchased Vehicles CSV</h1>
    <div class="row">
        <div class="col-lg-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('buy.copart') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="mb-3">
                                <img src="{{ asset('assets/img/copart.webp') }}" alt="My Image" width="180"
                                    style="padding-bottom: 25px;"><br>
                                <input type="file" name="csv_file" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary add-btn"><i class="align-middle"
                                    data-feather="upload"></i> Upload Copart File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('buy.iaai') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <div class="mb-3">
                                <img src="{{ asset('assets/img/iaai.webp') }}" alt="My Image" width="180"
                                    style="padding-bottom: 25px;"><br>
                                <input type="file" name="csv_file" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-danger add-btn"><i class="align-middle"
                                    data-feather="upload"></i> Upload IAA File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="loader" class="row text-center d-none">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Checking CSV File</h5>
                    <h6 class="card-subtitle text-muted">Please wait while we check your file and save it in the system.
                    </h6>
                </div>
                <div class="card-body">

                    <div class="mb-2">

                        <div class="spinner-border text-primary me-2" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($csv_header))
        <div class="row">
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Header must include following columns (order can be different)</h5>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width:40%;">Sr.#</th>
                                <th style="width:40%;">Column</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($csv_header as $key => $value)
                                <tr @if ($value == $column) style="background: #f5abab;" @endif>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $value }} </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection
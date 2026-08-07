@extends('layouts.app')

@section('title', 'Add File')

@section('scripts')
    <script>
        $('#add').click(function() {
            if ($('input[name="csv_file"]').val() != '') {
                $('#loader').toggleClass('d-none');
            }
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

    <h1 class="h3 mb-3"> Step 2: Upload Inventory CSV</h1>

    <div class="row">

        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('inventory.copart') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <div class="mb-3">
                                <img src="{{ asset('assets/img/copart.webp') }}" alt="My Image" width="180"
                                    style="padding-bottom: 25px;"><br>
                                <input type="file" name="csv_file" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <div class="mb-3">
                                    <label class="form-label w-100">Starting Row</label>
                                    <input type="number" name="start" value="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="mb-3">
                                    <label class="form-label w-100">Ending Row</label>
                                    <input type="number" name="end" value="0">
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <button type="submit" id="add" class="btn btn-lg btn-primary"><i class="align-middle"
                                    data-feather="upload"></i> Upload File
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
                        <h5 class="card-title">Expected Header for CSV File</h5>
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
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    @if (isset($past_auction_date))
        <div class="row">
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vehicles with past auction date</h5>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width:40%;">VIN</th>
                                <th style="width:25%">Auction date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($past_auction_date as $vehicle)
                                <tr>
                                    <td>{{ $vehicle['vin'] }}</td>
                                    <td>{{ $vehicle['auction_date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection
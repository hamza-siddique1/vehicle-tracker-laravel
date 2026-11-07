@extends('layouts.app')

@section('title', 'Add Vehicle')

@section('scripts')
    <script>
        $(document).ready(function () {

            $(".daterange").daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                startDate: moment(),
                locale: {
                    format: "Y-MM-DD"
                }
            });

        });


        $('#add_location').click(function () {
            var location = $('#new_location').val();

            if (location.length > 0) {

                let url = "{{ route('location.add', ':id' )  }}";
                url = url.replace(':id', location);

                console.log(url);

                $.ajax({
                    url: url,
                    success: function (msg) {
                        console.log("Success: " + msg);
                        window.notyf.open({
                            'type': 'success',
                            'message': "Location Added",
                            'duration': 10000,
                            'ripple': true,
                            'dismissible': true
                        });

                        $('#location').append(' <option value=" ' + location + ' "> ' + location + ' </option>');
                    },
                    error: function (err) {
                        console.log("Error sending data to server: " + err);
                    }
                });

            } else {
                alert('Please select any row');
            }


        });
    </script>

@endsection
@section('content')

    <h1 class="h3 mb-3">Add New Vehicle </h1>

    <div class="row">

        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="danger">{{ session('error') }}</x-alert>
                    @endif
                    @if(session('warning'))
                        <x-alert type="warning">{{ session('warning') }}</x-alert>
                    @endif
                    @if($errors->any())
                        <x-alert type="danger">{{ $errors->first() }}</x-alert>
                    @endif

                    <form method="post" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="number">VIN</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="vin"
                                        value="{{ old('vin') }}"
                                        placeholder="Enter VIN"
                                    />
                                </div>

                            </div>



                            <div class="col-4">
                                <div class="form-group">
                                    <label for="purchase_lot">Purchase Lot</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="purchase_lot"
                                        placeholder="Enter purchase lot"
                                    />

                                </div>

                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="auction_lot">Auction Lot</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="auction_lot"
                                        placeholder="Enter auction lot"
                                    />

                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="number"
                                        name="year"
                                        placeholder="Enter Year e.g. 2002"
                                    />
                                </div>

                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="make">Make</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="make"
                                        placeholder="Enter Make"
                                    />
                                </div>

                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="model">Model</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="model"
                                        placeholder="Enter Model"
                                    />
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="number">Invoice Date</label>

                                    <input
                                        class="form-control form-control-lg daterange"
                                        type="text"
                                        name="invoice_date"
                                        placeholder="Enter card number"
                                    />

                                </div>

                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="left_location">Left Location</label>
                                    <input
                                        class="form-control form-control-lg daterange"
                                        type="text"
                                        name="left_location"
                                        placeholder="Enter left location"
                                        value="{{ old('left_location' )}}"
                                    />

                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="invoice_amount">Invoice Amount($)</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="number"
                                        name="invoice_amount"
                                        placeholder="Enter Invoice Amount"
                                        value="{{ old('invoice_amount' )}}"
                                    />

                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input
                                        class="form-control form-control-lg"
                                        type="text"
                                        name="location"
                                        placeholder="Enter location"
                                        value="{{ old('location' )}}"
                                    />

                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="location">Notes</label>
                                    <textarea class="form-control form-control-lg" name="notes"></textarea>
                                </div>
                            </div>


                        </div>
                        <div class="form-group">
                            <button type="submit" id="add" class="btn btn-lg btn-primary">Add New
                                Vehicle
                            </button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>





@endsection

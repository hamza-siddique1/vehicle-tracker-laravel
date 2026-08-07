$(document).ready(function () {

    $('[data-target="#modal-vehicle-detail"]').css('color', '#495057');

    /**
     * Get Vehicle Detail Form.Modal when click on year-make-model
     */

    $('.vehicles-table tbody').on('click', 'tr', function() {

        $('#vehicle-detail-div').html(
            '<div class="text-center">Please wait... Data is loading<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>'
        );

        var vehicleId = '/vehicles/' + $(this).attr('id');
        $.get(vehicleId + '/html', function(response) {
            //replace ID with "Vehicle ID",keys with "Has Keys"

            //create array which contains keys and values, all the keys will be replaced by their respective values in the response html
            var replaceKeys = {
                'ID': 'Vehicle ID',
                'CREATED_AT': 'Date Entered',
                'DESCRIPTION': 'Year-Make-Model',
                'VIN': 'VIN Number',
                'PURCHASE_LOT': 'Purchase Lot Number',
                'DATE_PAID': 'Purchase Date',
                'INVOICE_AMOUNT': 'Purchase Amount($)',
                'LEFT_LOCATION': 'Left Location',
                'AUCTION_LOT': 'Auction Lot Number',
                'LOCATION': 'Current Location',
                'CLAIM_NUMBER': 'Claim Number',
                'STATUS': 'Current Status',
                'ODOMETER': 'Mileage',
                'ODOMETER_BRAND': 'Odometer',
                'PRIMARY_DAMAGE': 'PRIMARY DAMAGE',
                'SECONDARY_DAMAGE': 'SECONDARY DAMAGE',
                'KEYS': 'Has Keys',
                'DRIVABILITY_RATING': 'Engine',
                'DAYS_IN_YARD': 'Days In Yard',
                'SALE_TITLE_STATE': 'Sale Title State',
                'SALE_TITLE_TYPE': 'Sale Title Type',
            };
            var new_response = response.html;

            //iterate over the replaceKeys array and replace the keys with their respective values in the response html
            $.each(replaceKeys, function(key, value) {
                new_response = new_response.replace(key, value);
            });

            var requiredFields = ['vin', 'description', 'location'];
            $.each(requiredFields, function(key, value) {
                new_response = new_response.replace('name="' + value + '"',
                    'name="' + value +
                    '" required');
            });

            //Apply pattern to vin field
            new_response = new_response.replace('name="vin"',
                'name="vin" pattern="[A-Za-z0-9]+" title="Only alphanumeric characters are allowed"'
            );

            $('#vehicle-detail-div').html(new_response);

            //We are overriding select2 library
            $('.select2').select2({
                placeholder: "Select Location",
                tags: true,
                insertTag: function(data, tag) {
                    data.push(tag);
                }
            });

            var startDate;
            $('.daterange').each(function(index) {
                startDate = $(this).val();

                console.log(startDate);
                $(this).daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    startDate: startDate,
                    locale: {
                        format: "YYYY-MM-DD"
                    }
                });
            });

        });

        //Adding action attr to form
        $('#vehicle-detail-form').attr('action', vehicleId);
    });


    //It triggers when we click on Upadte button on vehicle detail page
    $('.vehicle-detail-form').on('submit', function(e) {

        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var method = form.attr('method');
        var data = form.serialize();
        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {

                if (response.status == 'success') {
                    Swal.fire(
                        'Success!',
                        response.message,
                        'success'
                    );

                    $('#modal-vehicle-create').modal('hide');
                    $('#modal-vehicle-detail').modal('hide');
                    // window.location.href = '/dashboard';
                } else {
                    Swal.fire(
                        'Error!',
                        'Something went wrong',
                        'error'
                    );
                    console.log(response.message);


                }


            },
            error: function(error) {

                const errors = error.responseJSON.errors;
                var errorString = '';
                $.each(errors, function(key, value) {
                    errorString += '<li>' + value + '</li>';
                });


                Swal.fire(
                    'Error!',
                    errorString,
                    'error'
                );

            }
        });
    });
});

@extends('layouts.app')

@section('title', __('Run Lists'))

@section('scripts')

    <script src="{{ asset('/assets/js/custom.js') }}"></script>
    <script>
        var currentPage = 1;
        var totalPages = 1;
        var perPage = 10;
        var sortColumn = 'item_number';
        var sortOrder = 'asc';
        var filters = {};

        function fetchData(page, filters, sortColumn, sortOrder) {
            console.log('Sorting by', sortColumn, 'in', sortOrder, 'order');
            var requestData = {
                page: page,
                per_page: perPage
            };

            Object.keys(filters).forEach((key) => {
                if (filters[key] !== '-100') {
                    requestData[`filter[${key}]`] = filters[key];
                }
            });

            if (sortColumn != undefined) {
                //Update sorting order based on defaultSort and defaultOrder
                requestData['sort'] = (sortOrder === 'desc' ? '-' : '') + sortColumn;
            }


            $.ajax({
                url: 'get_run_lists',
                method: 'GET',
                data: requestData,
                dataType: 'json',
                success: function(response) {
                    totalPages = response.meta.last_page;
                    populateTable(response.data);
                    updatePaginationButtons(response.links, response.meta.links);
                    updateTableInfo(response.meta);

                },
                error: function() {
                    alert('Failed to fetch run lists from the API.');
                },

            });
        }

        function populateTable(runLists) {
            var tbody = $('#runs-table tbody');
            tbody.empty();

            if (runLists.length === 0) {
                displayNoRecordsMessage(11);
            }

            $.each(runLists, function(index, vehicle) {
                var row = '<tr>' +
                    '<td>' + vehicle.item_number + '</td>' +
					'<td><a href="https://www.copart.com/lot/' + vehicle.lot_number + '" target="_blank">' + vehicle.lot_number + '</a></td>' +
                    '<td>' + vehicle.claim_number + '</td>' +
                    '<td>' + vehicle.description + '</td>' +
                    '<td>' + vehicle.number_of_runs + '</td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        $(document).ready(function() {

            fetchData(currentPage, filters, sortColumn, sortOrder);

            $(document).on('click', '.delete-btn', function() {
                var resourceId = $(this).data('id');
                var csrfToken = '{{ csrf_token() }}';
                deleteConfirmation(resourceId, 'vehicle', 'runLists', csrfToken);
            });


            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                var description = $('[name="description"]').val();
                var item_number = $('[name="item_number"]').val();
                var lot_number = $('[name="lot_number"]').val();
                var claim_number = $('[name="claim_number"]').val();
                var number_of_runs = $('[name="number_of_runs"]').val();

                filters = {
                    description: description,
                    item_number: item_number,
                    lot_number: lot_number,
                    claim_number: claim_number,
                    number_of_runs: number_of_runs,

                };
                currentPage = 1;
                fetchData(currentPage, filters, sortColumn, sortOrder);
            });

            // Handle header click to change sorting
            $('#runs-table th').on('click', function() {
                sortColumn = $(this).data('column');
                sortOrder = 'asc';

                // Toggle sorting order
                if ($(this).hasClass('sorting_asc')) {
                    sortOrder = 'desc';
                }

                // Update sorting icons
                $('#runs-table th').removeClass('sorting_asc sorting_desc');
                $(this).addClass('sorting_' + sortOrder);

                // Sort the table
                fetchData(currentPage, filters, sortColumn, sortOrder);
            });

            $('#export-btn').on('click', function() {
                var filters = {
                    description: $('[name="description"]').val(),
                    item_number: $('[name="item_number"]').val(),
                    lot_number: $('[name="lot_number"]').val(),
                    claim_number: $('[name="claim_number"]').val(),
                    number_of_runs: $('[name="number_of_runs"]').val(),
                    number_of_runs: $('[name="number_of_runs"]').val(),
                };

                var requestData = {};

                Object.keys(filters).forEach((key) => {
                    if (filters[key] !== '-100') {
                        requestData[`filter[${key}]`] = filters[key];
                    }
                });

                if (sortColumn != undefined) {
                    //Update sorting order based on defaultSort and defaultOrder
                    requestData['sort'] = (sortOrder === 'desc' ? '-' : '') + sortColumn;
                }

                console.log(requestData);
                var url = '/export_run_list'; // Update the URL based on your routes

                // Append filters to the URL
                url += '?' + $.param(requestData);
                console.log(url);
                // Open the generated PDF in a new tab or window
                window.open(url, '_blank');
            });
        });
    </script>
@endsection

@section('content')

    <h1 class="h3 mb-3">KAJ Run List</h1>
    @include('pages.runlist.filters')



    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="datatables-reponsive_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="table_length" id="table_length"><label>Show <select
                                            name="datatables-reponsive_length" id="per-page-select"
                                            class="form-select form-select-sm">

                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="-1">All</option>
                                        </select> entries</label></div>
                            </div>

                        </div>
                        <div class="row dt-row">
                            <div class="col-sm-12">
                                <table id="runs-table" class="table table-striped dataTable table-hover no-footer dtr-inline"
                                    style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th data-column="item_number" class="sorting_asc">Item #</th>
                                            <th data-column="lot_number">Lot #</th>
                                            <th data-column="claim_number">Claim #</th>
                                            <th data-column="description">Description</th>
                                            <th data-column="number_of_runs"># of Runs</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="table_entries_info" role="status" aria-live="polite">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="datatables-reponsive_paginate">
                                    <ul class="pagination"></ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@extends('layouts.app')

@section('title', 'Vehicles')
@php
$role = Auth()->user()->role;
$query = str_replace(url()->current(), '',url()->full());
@endphp

@section('styles')
<style>
.modal-body {
    padding: 0rem !important;
}
</style>
@endsection

@section('content')
@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@elseif(session('error'))
<x-alert type="error">{{ session('error') }}</x-alert>
@elseif(session('warning'))
<x-alert type="warning">{{ session('warning') }}</x-alert>
@endif


<h1 class="h3 mb-3">All Orders</h1>

{{--    @include('pages.order._inc.stats')--}}


@include('pages.vehicle.filters.filters2')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <table id="vehicles-table" class="table table-striped dataTable no-footer dtr-inline"
                    style="width:100%">
                    <thead>
                        <tr>

                            <th>Order #</th>
                            <th>Etsy Link</th>
                            <th>Text Status</th>
                            <th>Design Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>35</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/35</a>

                            </td>
                            <td><button class="btn btn-pill btn-danger">Non Verified</button></td>
                            <td><button class="btn btn-pill btn-success">Generated</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>
                        <tr class="odd">
                            <td>33</td>
                            <td class="dtr-control sorting_1" tabindex="0">
                                <a> etsy.com/33</a>

                            </td>
                            <td><button class="btn btn-pill btn-success">Verified</button></td>
                            <td><button class="btn btn-pill btn-warning">In Progress</button></td>

                            <td class="table-action">
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-2 align-middle">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trash align-middle">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg></a>
                            </td>

                        </tr>




                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
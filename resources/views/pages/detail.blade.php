@extends('layouts.app')

@section('title', 'Vehicle Details')


@section('content')

<h1 class="h3 mb-3"> Order Details </h1>


<div class="row">
    <div class="col-xl-8">

        <div class="card">
            <img class="d-block w-100"
                src="https://hereyougopup.com/cdn/shop/files/EtsyLeashAndCollar_0161_cb07f253-d01c-43ba-b2ec-7751f8ce70d5.jpg?v=1687718535&width=1080"
                alt="1">
        </div>

    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">

                <h5 class="card-title mb-0 text-center">Md Wide</h5>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    <div class="col-sm-3 col-xl-12 col-xxl-3 text-center">
                        <label class="form-label" for="location"> Leash Length </label>
                    </div>
                    <div class="col-sm-9 col-xl-12 col-xxl-9">
                        <div class="form-group">

                            <select name="location" id="design_status"
                                class="form-control form-select custom-select select2" data-toggle="select2">
                                <option> Select Leash Length</option>
                                <option> 4</option>
                                <option selected> 6</option>
                                <option> 8</option>

                            </select>
                        </div>
                    </div>
                </div>

                <table class="table table-sm my-2">
                    <tbody>
                        <tr>
                            <td>Color</td>
                            <th>Gold</th>
                        </tr>
                        <tr>
                            <td>Style</td>
                            <th>1178</th>
                        </tr>
                        <tr>
                            <td>Font</td>
                            <th>E</th>
                        </tr>


                    </tbody>
                </table>

                <hr>

                <strong>Personalization</strong>

                <textarea rows="2" class="form-control" id="inputBio" placeholder="Tell something about yourself"
                    spellcheck="false" style="height: 150px;">Dusty
My Good boy
Dusty</textarea>


                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Text Status</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" value="option1" name="radios-example">
                                <span class="form-check-label">
                                    Click here if you want to mark this order as text-verified
                                </span>
                            </label>
                        </div>

                    </div>
                </div>


            </div>
        </div>
        @endsection
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form>
                    <input type="hidden" class="d-none" name="filter" value="true" hidden>
                    <div class="row">

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="location"> Text Status </label>
                                <select name="location" id="text_status"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="-100"> Select Verification Status</option>

                                    <option> Verified</option>
                                    <option> Non - verified</option>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="location"> Design File </label>
                                <select name="location" id="design_status"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="-100"> Select file Status</option>

                                    <option> Generated</option>
                                    <option> In Progress</option>
                                    <option> Re - Run</option>

                                </select>
                            </div>
                        </div>








                    </div>

                    <div class="row">
                        <div class="col-sm mt-4">
                            <button type="button"
                                class="btn btn-sm btn-primary apply-dt-filters mt-2">{{ __('Apply') }}</button>
                            <button type="button"
                                class="btn btn-sm btn-secondary clear-dt-filters mt-2">{{ __('Clear') }}</button>


                        </div>
                    </div>


                </form>

            </div>
        </div>
    </div>
</div>
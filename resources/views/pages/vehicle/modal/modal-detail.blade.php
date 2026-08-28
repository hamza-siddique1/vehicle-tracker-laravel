<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="modal fade" id="modal-vehicle-detail" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <form class="vehicle-detail-form" id="vehicle-detail-form" method="post" action="#">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Vehicle Details</h5>
                            </div>
                            <div id="vehicle-detail-div"></div>
                            <div class="modal-footer">
                                <a href="#"
                                    class="btn btn-success mr-auto" id="create-ndtc-btn">
                                        <i class="fa fa-paper-plane me-1"></i> Create NDTC Order
                                </a>
                                <button type="button" class="btn btn-secondary" id="close-modal-btn"
                                        data-dismiss="modal">Close
                                </button>
                                <button type="submit" class="btn btn-primary"
                                        @if($role == 'viewer')
                                            disabled
                                    @endif
                                >Save changes
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['total'] }} Total Orders</h3>
                        <p class="mb-0 text-muted">NDTC electronic title orders</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-file-alt fa-2x text-secondary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['draft'] }} Draft</h3>
                        <p class="mb-0 text-muted">Not yet submitted</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-pencil-alt fa-2x text-secondary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['ready_for_docs'] }} Ready for Docs</h3>
                        <p class="mb-0 text-warning">Waiting on document upload</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-upload fa-2x text-warning"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['ready_to_finalize'] }} Ready to Finalize</h3>
                        <p class="mb-0 text-success">Action needed — finalize now</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-flag-checkered fa-2x text-success"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['processing'] }} Processing</h3>
                        <p class="mb-0 text-primary">With CHAMP / DMV or under review</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-spinner fa-2x text-primary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['on_hold_aging'] }} On Hold / Aging</h3>
                        <p class="mb-0 text-warning">Stuck — needs attention</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-hourglass-half fa-2x text-warning"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['completed'] }} Completed</h3>
                        <p class="mb-0 text-success">Cleared and completed</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-check-circle fa-2x text-success"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['rejected'] }} Rejected</h3>
                        <p class="mb-0 text-danger">Needs a fix</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-exclamation-circle fa-2x text-danger"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['canceled'] }} Cancelled</h3>
                        <p class="mb-0 text-muted">Withdrawn or voided</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-ban fa-2x text-secondary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['manual_review'] }} Manual Review</h3>
                        <p class="mb-0 text-warning">Flagged for human review</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-user-check fa-2x text-warning"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="media">
                    <div class="media-body">
                        <h3 class="mb-2">{{ $stats['title_terminated'] }} Title Terminated</h3>
                        <p class="mb-0 text-dark">No longer valid</p>
                    </div>
                    <div class="d-inline-block ml-3">
                        <div class="stat"><i class="fas fa-times-circle fa-2x text-dark"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

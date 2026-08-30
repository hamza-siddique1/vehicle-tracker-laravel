{{-- ══ FILTERS ═════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('ndtc.orders.index') }}" id="filterForm">

                    <div class="row">

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="search">Search</label>
                                <input id="search" class="form-control" type="text" name="search"
                                    value="{{ request('search') }}" placeholder="VIN, Order ID, Title #…" />
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="status">Status</label>
                                <select name="status" id="status"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="">All statuses</option>
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ request('status') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="transaction_type">Type</label>
                                <select name="transaction_type" id="transaction_type"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="">All types</option>
                                    @foreach(config('ndtc.transaction_types') as $code => $label)
                                        <option value="{{ $code }}" {{ request('transaction_type') === $code ? 'selected' : '' }}>
                                            {{ $label }} ({{ $code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="date_from">Transfer date from</label>
                                <input id="date_from" class="form-control" type="date" name="date_from"
                                    value="{{ request('date_from') }}" />
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="date_to">Transfer date to</label>
                                <input id="date_to" class="form-control" type="date" name="date_to"
                                    value="{{ request('date_to') }}" />
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group d-flex" style="gap:1rem">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox"
                                        name="has_rejections" value="1" id="has_rejections"
                                        {{ request('has_rejections') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_rejections">
                                        Has rejections
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox"
                                        name="finalized" value="1" id="finalized"
                                        {{ request('finalized') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="finalized">
                                        Finalized only
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox"
                                        name="needs_action" value="1" id="needs_action"
                                        {{ request('needs_action') ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger" for="needs_action">
                                        Needs action only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm mt-4">
                            <button type="submit" class="btn btn-sm btn-primary mt-2">{{ __('Apply') }}</button>
                            <a href="{{ route('ndtc.orders.index') }}" class="btn btn-sm btn-secondary mt-2">{{ __('Clear') }}</a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

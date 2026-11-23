{{-- _disposing-entity.blade.php --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="align-middle mr-2" data-feather="building"></i>
            Disposing Entity (Auction) <span class="required-note">*</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-secondary small">
            <i class="align-middle mr-1" data-feather="info"></i>
            Auction companies do not need to be pre-registered with CHAMP.
            They can be submitted ad-hoc on each order. Enter the auction branch address.
        </div>

        <div class="form-row">
            {{-- Entity Type (Hidden, default: company) --}}
            <input type="hidden" name="disposing_type" value="company">

            {{-- Name --}}
            <div class="form-group col-md-4">
                <label>Entity Name <span class="required-note">*</span></label>
                <input type="text"
                       class="form-control @error('disposing_name') is-invalid @enderror"
                       name="disposing_name"
                       value="{{ $val('disposing_name', $auctionSource) }}"
                       placeholder="e.g. Copart Inc.">
                @error('disposing_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text">
                    <span class="field-badge badge-auto">AUTO</span>
                    From <code>vehicles.source</code>
                </small>
            </div>

            {{-- Address 1 --}}
            <div class="form-group col-md-5">
                <label>Address Line 1 <span class="required-note">*</span></label>
                <input type="text"
                       class="form-control @error('disposing_address1') is-invalid @enderror"
                       name="disposing_address1"
                       value="{{ $val('disposing_address1') }}"
                       placeholder="Street address of auction location">
                @error('disposing_address1')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text">
                    <span class="field-badge badge-empty">REQUIRED</span>
                    Enter auction branch address
                </small>
            </div>

            {{-- Address 2 --}}
            <div class="form-group col-md-3">
                <label>Address Line 2 <span class="badge badge-optional">OPTIONAL</span></label>
                <input type="text"
                       class="form-control"
                       name="disposing_address2"
                       value="{{ $val('disposing_address2') }}"
                       placeholder="Suite, unit (optional)">
            </div>
        </div>

        <div class="form-row">
            {{-- City --}}
            <div class="form-group col-md-4">
                <label>City <span class="required-note">*</span></label>
                <input type="text"
                       class="form-control @error('disposing_city') is-invalid @enderror"
                       name="disposing_city"
                       value="{{ $val('disposing_city') }}">
                @error('disposing_city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- State --}}
            <div class="form-group col-md-3">
                <label>State <span class="required-note">*</span></label>
                <select class="form-control @error('disposing_state') is-invalid @enderror"
                        name="disposing_state">
                    <option value="">-- State --</option>
                    @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $state)
                        <option value="{{ $state }}" {{ $val('disposing_state') == $state ? 'selected' : '' }}>
                            {{ $state }}
                        </option>
                    @endforeach
                </select>
                @error('disposing_state')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ZIP --}}
            <div class="form-group col-md-2">
                <label>ZIP Code <span class="required-note">*</span></label>
                <input type="text"
                       class="form-control @error('disposing_zip') is-invalid @enderror"
                       name="disposing_zip"
                       value="{{ $val('disposing_zip') }}"
                       placeholder="00000"
                       maxlength="10">
                @error('disposing_zip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- County --}}
            <div class="form-group col-md-3">
                <label>County <span class="badge badge-optional">OPTIONAL</span></label>
                <input type="text"
                       class="form-control"
                       name="disposing_county"
                       value="{{ $val('disposing_county') }}"
                       placeholder="Optional">
            </div>
        </div>

        {{-- Phone (Optional) --}}
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Phone Number <span class="badge badge-optional">OPTIONAL</span></label>
                <input type="text"
                       class="form-control"
                       name="disposing_phone"
                       value="{{ $val('disposing_phone') }}"
                       placeholder="555-555-5555">
            </div>
        </div>
    </div>
</div>

{{-- Suggest saved disposing entities while typing in any major field; picking one fills the whole entity --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // form input name => column searched on the server
    var searchable = {
        disposing_name: 'name',
        disposing_address1: 'address_line_1',
        disposing_city: 'city',
        disposing_zip: 'zip',
        disposing_phone: 'phone_number'
    };
    var map = {
        name: 'disposing_name',
        address_line_1: 'disposing_address1', address_line_2: 'disposing_address2',
        city: 'disposing_city', state: 'disposing_state', zip: 'disposing_zip',
        county: 'disposing_county', phone_number: 'disposing_phone'
    };
    var searchUrl = @json(route('ndtc.entities.search'));

    Object.keys(searchable).forEach(function (inputName) {
        var input = document.querySelector('[name="' + inputName + '"]');
        if (!input) return;

        var box = document.createElement('div');
        box.className = 'list-group shadow-sm';
        box.style.cssText = 'position:absolute;z-index:1050;display:none;min-width:320px;max-height:260px;overflow:auto';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(box);
        var timer = null;

        function hide() { box.style.display = 'none'; box.innerHTML = ''; }

        function fill(entity) {
            Object.keys(map).forEach(function (key) {
                var el = document.querySelector('[name="' + map[key] + '"]');
                if (el) el.value = entity[key] || '';
            });
            hide();
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) { hide(); return; }
            timer = setTimeout(function () {
                var url = searchUrl + '?field=' + encodeURIComponent(searchable[inputName]) + '&q=' + encodeURIComponent(q);
                fetch(url, {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    credentials: 'same-origin'
                }).then(function (r) { return r.ok ? r.json() : []; }).then(function (rows) {
                    box.innerHTML = '';
                    if (!rows.length) { hide(); return; }
                    rows.forEach(function (row) {
                        var item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action py-2';
                        var title = document.createElement('strong');
                        title.textContent = row.name;
                        var sub = document.createElement('div');
                        sub.className = 'small text-muted';
                        sub.textContent = [row.address_line_1, row.city, row.state, row.zip, row.phone_number].filter(Boolean).join(', ');
                        item.appendChild(title);
                        item.appendChild(sub);
                        item.addEventListener('mousedown', function (e) { e.preventDefault(); fill(row); });
                        box.appendChild(item);
                    });
                    box.style.display = 'block';
                }).catch(hide);
            }, 250);
        });

        input.addEventListener('blur', function () { setTimeout(hide, 150); });
    });
});
</script>

@php
    $assignableSales = \App\Models\User::leadAssignees()->orderBy('name')->get(['id', 'name', 'email']);
@endphp
<div class="modal fade" id="assignLeadModal" tabindex="-1" aria-labelledby="assignLeadTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="assignLeadForm">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="assignLeadTitle">Assign lead</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p id="assignLeadName" class="font-weight-bold"></p>
                    <label for="assignLeadUser">Sales representative</label>
                    <select id="assignLeadUser" name="assigned_to" class="form-control" required>
                        <option value="">Select sales representative...</option>
                        @foreach($assignableSales as $salesUser)
                            <option value="{{ $salesUser->id }}">{{ $salesUser->name }} — {{ $salesUser->email }}</option>
                        @endforeach
                    </select>
                    @if($assignableSales->isEmpty())
                        <p class="text-muted mt-2">No active staff with Leads Read permission are available.</p>
                    @endif
                    <small class="text-muted d-block mt-2">This changes the lead owner. Sales users without View All access only their assigned leads.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" @disabled($assignableSales->isEmpty())>Save assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $('#assignLeadModal').on('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) return;
        document.getElementById('assignLeadForm').action = trigger.dataset.url;
        document.getElementById('assignLeadName').textContent = trigger.dataset.name;
        document.getElementById('assignLeadUser').value = trigger.dataset.assigned || '';
        $(this).find('button[type="submit"]').prop('disabled', !document.getElementById('assignLeadUser').options.length || document.getElementById('assignLeadUser').options.length === 1);
    }).on('shown.bs.modal', function () {
        document.getElementById('assignLeadUser').focus();
    });
    $('#assignLeadForm').on('submit', function () {
        $(this).find('button[type="submit"]').prop('disabled', true).text('Saving...');
    });
</script>
@endpush

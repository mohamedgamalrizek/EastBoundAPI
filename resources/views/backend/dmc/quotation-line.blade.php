<tr>
    <td><select name="items[{{ $index }}][service_type]" class="form-control" required>@foreach(['hotel' => 'Hotel', 'transport' => 'Transport', 'guide' => 'Guide', 'activity' => 'Activity / Ticket', 'domestic_flight' => 'Domestic Flight'] as $key => $label)<option value="{{ $key }}" @selected(($line['service_type'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></td>
    <td><input name="items[{{ $index }}][description]" value="{{ $line['description'] ?? '' }}" class="form-control" required maxlength="1000"></td>
    <td><input type="date" name="items[{{ $index }}][service_date]" value="{{ isset($line['service_date']) ? substr($line['service_date'], 0, 10) : '' }}" class="form-control" required></td>
    <td><select name="items[{{ $index }}][supplier_id]" class="form-control"><option value="">Not selected</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((string) ($line['supplier_id'] ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></td>
    <td><input type="number" min="1" max="10000" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? 1 }}" class="form-control" required></td>
    <td><input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_cost]" value="{{ $line['unit_cost'] ?? '' }}" class="form-control" required></td>
    <td><input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $line['unit_price'] ?? '' }}" class="form-control" required></td>
    <td><button type="button" class="btn btn-outline-danger" data-remove-line>Remove</button></td>
</tr>

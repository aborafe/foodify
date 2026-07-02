@php
    $sections = old('included_sections', $report ? collect($report->included_sections)->join(', ') : 'Revenue trend, order status mix, top selling meals');
@endphp

<div class="form-grid">
    <label class="form-field">
        <span>Report Name</span>
        <input name="name" value="{{ old('name', $report->name ?? 'Monthly Revenue Summary') }}" required>
    </label>
    <label class="form-field">
        <span>Metric</span>
        <select name="metric" required>
            @foreach (['sales' => 'Sales', 'orders' => 'Orders', 'customers' => 'Customers', 'meals' => 'Meals'] as $value => $label)
                <option value="{{ $value }}" @selected(old('metric', $report->metric ?? 'sales') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="form-field">
        <span>Date Range</span>
        <input name="date_range" value="{{ old('date_range', $report->date_range ?? 'This Month') }}" required>
    </label>
    <label class="form-field">
        <span>Export Format</span>
        <select name="export_format" required>
            @foreach (['pdf' => 'PDF', 'csv' => 'CSV', 'xlsx' => 'XLSX'] as $value => $label)
                <option value="{{ $value }}" @selected(old('export_format', $report->export_format ?? 'pdf') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="form-field">
        <span>Status</span>
        <select name="status" required>
            @foreach (['active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $report->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="form-field full">
        <span>Included Sections</span>
        <textarea name="included_sections">{{ $sections }}</textarea>
    </label>
</div>

@php
    $selectedUser = old('user_id', $order?->user_id);
    $selectedRows = collect(old('items', $order?->orderItems?->map(fn ($item) => [
        'meal_id' => $item->meal_id,
        'quantity' => $item->quantity,
    ])->values()->all() ?? []))->keyBy('meal_id');
    $mealLookup = $meals->keyBy('id');
@endphp

<div class="form-grid">
    <label class="form-field full customer-combobox">
        <span>Customer</span>
        <div class="customer-search-row">
            <input
                type="search"
                data-customer-search
                placeholder="Search by full name, partial name, +20101891997, or 01018919997"
                autocomplete="off"
                value="{{ $order?->user ? $order->user->full_name.' - '.$order->user->phone : '' }}"
            >
            <button class="crud-button ghost" type="button" data-customer-clear>Change</button>
        </div>
        <input data-customer-id name="user_id" type="hidden" value="{{ $selectedUser }}">
        <div class="customer-results" data-customer-results>
            @foreach($customers as $customer)
                @php
                    $digits = preg_replace('/\D+/', '', $customer->phone ?? '');
                    $localPhone = str_starts_with($digits, '20') ? '0'.substr($digits, 2) : $digits;
                    $searchText = str($customer->full_name.' '.$customer->email.' '.$customer->phone.' '.$digits.' '.$localPhone)->lower();
                @endphp
                <button
                    type="button"
                    data-customer-option
                    data-customer-id="{{ $customer->id }}"
                    data-customer-label="{{ $customer->full_name }} - {{ $customer->phone }}"
                    data-customer-search="{{ $searchText }}"
                    data-customer-phone="{{ $digits }}"
                    data-customer-local-phone="{{ $localPhone }}"
                    data-customer-name="{{ str($customer->full_name)->lower() }}"
                >
                    <strong>{{ $customer->full_name }}</strong>
                    <span>{{ $customer->phone }} {{ $customer->email ? '- '.$customer->email : '' }}</span>
                </button>
            @endforeach
        </div>
    </label>

    <label class="form-field"><span>Order Status</span><select name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $order?->status ?? 'pending') === $status)>{{ str($status)->replace('_', ' ')->headline() }}</option>@endforeach</select></label>
    <label class="form-field"><span>Payment Status</span><select name="payment_status">@foreach($paymentStatuses as $status)<option value="{{ $status }}" @selected(old('payment_status', $order?->payment_status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
    <label class="form-field"><span>Delivery Fee</span><input name="delivery_fee" type="number" min="0" step="0.01" value="{{ old('delivery_fee', $order?->delivery_fee ?? 30) }}"></label>
    <label class="form-field"><span>Manual Adjustment</span><input name="manual_adjustment" type="number" step="0.01" value="{{ old('manual_adjustment', $order?->manual_adjustment ?? 0) }}"></label>
    <label class="form-field"><span>Estimated Minutes</span><input name="estimated_delivery_time" type="number" min="1" max="300" value="{{ old('estimated_delivery_time', $order?->estimated_delivery_time ?? 45) }}"></label>
    <label class="form-field full"><span>Delivery Address</span><input name="delivery_address" value="{{ old('delivery_address', $order?->delivery_address) }}"></label>
    <label class="form-field full"><span>Order Notes</span><textarea name="notes" placeholder="Add kitchen, delivery, or customer notes">{{ old('notes', $order?->notes) }}</textarea></label>
</div>

<section class="meal-picker order-meal-builder" data-order-meal-builder>
    <div class="panel-header compact">
        <h2>Order Meals</h2>
        <span>Search a meal, add it as a row, then edit quantity or remove it.</span>
    </div>
    <div class="meal-search-row">
        <label class="form-field">
            <span>Meal Name</span>
            <input type="search" data-meal-search placeholder="Type meal name to filter">
        </label>
        <div class="meal-search-results" data-meal-results>
            @foreach($meals as $meal)
                <button
                    type="button"
                    data-meal-option
                    data-meal-id="{{ $meal->id }}"
                    data-meal-name="{{ $meal->name }}"
                    data-meal-price="{{ $meal->price }}"
                    data-meal-search="{{ str($meal->name)->lower() }}"
                >
                    <strong>{{ $meal->name }}</strong>
                    <span>${{ number_format((float) $meal->price, 2) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="order-items-table-wrap">
        <table class="order-items-table">
            <thead>
                <tr><th>Meal</th><th>Unit Price</th><th>Qty</th><th>Line Total</th><th>Action</th></tr>
            </thead>
            <tbody data-order-items-body>
                @foreach($selectedRows->values() as $index => $selectedRow)
                    @php($meal = $mealLookup->get((int) $selectedRow['meal_id']))
                    @if($meal)
                        <tr data-order-item-row data-meal-id="{{ $meal->id }}" data-meal-price="{{ $meal->price }}">
                            <td>
                                <input type="hidden" name="items[{{ $index }}][meal_id]" value="{{ $meal->id }}" data-item-meal-id>
                                <strong>{{ $meal->name }}</strong>
                            </td>
                            <td>${{ number_format((float) $meal->price, 2) }}</td>
                            <td><input class="quantity-cell" name="items[{{ $index }}][quantity]" data-item-quantity type="number" min="1" max="99" value="{{ $selectedRow['quantity'] ?? 1 }}"></td>
                            <td data-item-total>${{ number_format((float) $meal->price * (int) ($selectedRow['quantity'] ?? 1), 2) }}</td>
                            <td><button class="icon-action danger" data-remove-order-item type="button" aria-label="Remove meal">×</button></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <p class="empty-order-items" data-empty-order-items @class(['is-hidden' => $selectedRows->isNotEmpty()])>No meals selected yet.</p>
    </div>

    <template data-order-item-template>
        <tr data-order-item-row data-meal-id="" data-meal-price="">
            <td>
                <input type="hidden" name="" value="" data-item-meal-id>
                <strong data-item-name></strong>
            </td>
            <td data-item-price></td>
            <td><input class="quantity-cell" name="" data-item-quantity type="number" min="1" max="99" value="1"></td>
            <td data-item-total></td>
            <td><button class="icon-action danger" data-remove-order-item type="button" aria-label="Remove meal">×</button></td>
        </tr>
    </template>
</section>

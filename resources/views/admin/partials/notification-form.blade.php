<div class="form-grid">
    @if($creating)
        <label class="form-field"><span>Audience</span><select name="audience"><option value="single">Single customer</option><option value="all">All active customers</option></select></label>
    @else
        <input name="audience" type="hidden" value="single">
    @endif
    <label class="form-field"><span>Customer</span><select name="user_id">@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((int) old('user_id', $notification?->user_id) === $customer->id)>{{ $customer->full_name }} - {{ $customer->phone }}</option>@endforeach</select></label>
    <label class="form-field"><span>Type</span><select name="type">@foreach($types as $type)<option value="{{ $type }}" @selected(old('type', $notification?->type ?? 'system') === $type)>{{ str($type)->replace('_', ' ')->headline() }}</option>@endforeach</select></label>
    <label class="form-field full"><span>Title</span><input name="title" value="{{ old('title', $notification?->title) }}"></label>
    <label class="form-field full"><span>Message</span><textarea name="body">{{ old('body', $notification?->body) }}</textarea></label>
    <label class="form-field full"><span>Image URL</span><input name="image" value="{{ old('image', $notification?->image) }}"></label>
    <label class="form-field checkbox-field"><input name="is_read" type="checkbox" value="1" @checked(old('is_read', $notification?->is_read))> Mark as read</label>
</div>

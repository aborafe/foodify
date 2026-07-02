<div class="form-grid">
    <label class="form-field"><span>Full Name</span><input name="full_name" value="{{ old('full_name', $customer?->full_name) }}"></label>
    <label class="form-field"><span>Phone</span><input name="phone" value="{{ old('phone', $customer?->phone) }}"></label>
    <label class="form-field"><span>Email</span><input name="email" type="email" value="{{ old('email', $customer?->email) }}"></label>
    <label class="form-field"><span>Birth Date</span><input name="birth_date" type="date" value="{{ old('birth_date', $customer?->birth_date?->format('Y-m-d')) }}"></label>
    <label class="form-field full"><span>Image URL</span><input name="image" value="{{ old('image', $customer?->image) }}"></label>
    <label class="form-field full"><span>Address</span><textarea name="address">{{ old('address', $customer?->address) }}</textarea></label>
    <label class="form-field"><span>{{ $customer ? 'New Password' : 'Password' }}</span><input name="password" type="password" placeholder="{{ $customer ? 'Leave blank to keep current password' : '' }}"></label>
    <label class="form-field"><span>Confirm Password</span><input name="password_confirmation" type="password"></label>
    <label class="form-field checkbox-field"><input name="phone_verified_at" type="checkbox" value="1" @checked(old('phone_verified_at', (bool) $customer?->phone_verified_at))> Phone verified</label>
    <label class="form-field checkbox-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $customer?->is_active ?? true))> Active customer</label>
</div>

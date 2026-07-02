@php($category ??= null)

<div class="form-grid">
    <label class="form-field">
        <span>Category Name</span>
        <input name="name" value="{{ old('name', $category?->name) }}" required>
    </label>
    <label class="form-field full upload-field">
        <span>صورة الصنف</span>
        @if($category?->image)
            <span class="current-upload">
                <img src="{{ $category->image }}" alt="{{ $category->name }}">
                <small>اترك الحقل فارغًا للاحتفاظ بالصورة الحالية.</small>
            </span>
        @else
            <small>ارفع صورة JPG أو PNG أو WEBP بحجم لا يتجاوز 2MB.</small>
        @endif
        <input name="image" type="file" accept="image/jpeg,image/png,image/webp">
    </label>
    <label class="form-field full checkbox-field">
        <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $category?->is_active ?? true))>
        Active category
    </label>
</div>

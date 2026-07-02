@php
    $nutritionValue = old('nutrition', $meal?->nutrition);
    $ingredientsValue = old('ingredients', $meal?->ingredients);

    $nutrition = is_string($nutritionValue) ? json_decode($nutritionValue, true) : $nutritionValue;
    $ingredients = is_string($ingredientsValue) ? json_decode($ingredientsValue, true) : $ingredientsValue;

    $nutrition = is_array($nutrition) ? $nutrition : [];
    $ingredients = collect(is_array($ingredients) ? $ingredients : [])
        ->filter(fn ($ingredient) => filled($ingredient))
        ->values();

    $nutritionFields = [
        'calories' => 'السعرات الحرارية',
        'protein' => 'البروتين',
        'carbs' => 'الكربوهيدرات',
        'fat' => 'الدهون',
        'fiber' => 'الألياف',
    ];
@endphp

<div class="form-grid product-form-grid" data-product-form>
    <label class="form-field"><span>اسم الوجبة</span><input name="name" value="{{ old('name', $meal?->name) }}"></label>
    <label class="form-field"><span>التصنيف</span><select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int) old('category_id', $meal?->category_id) === $category->id)>{{ $category->name }}</option>@endforeach</select></label>
    <label class="form-field"><span>السعر</span><input name="price" type="number" min="0" step="0.01" value="{{ old('price', $meal?->price) }}"></label>
    <label class="form-field"><span>التقييم</span><input name="rating" type="number" min="0" max="5" step="0.1" value="{{ old('rating', $meal?->rating ?? 0) }}"></label>
    <label class="form-field full upload-field">
        <span>صورة الوجبة</span>
        @if($meal?->image)
            <span class="current-upload">
                <img src="{{ $meal->image }}" alt="{{ $meal->name }}">
                <small>اترك الحقل فارغًا للاحتفاظ بالصورة الحالية.</small>
            </span>
        @else
            <small>ارفع صورة JPG أو PNG أو WEBP بحجم لا يتجاوز 2MB.</small>
        @endif
        <input name="image" type="file" accept="image/jpeg,image/png,image/webp">
    </label>
    <label class="form-field full"><span>الوصف</span><textarea name="description">{{ old('description', $meal?->description) }}</textarea></label>

    <section class="json-editor-card full" data-nutrition-editor>
        <div class="json-editor-header">
            <h3>القيم الغذائية</h3>
            <p>اكتب الأرقام فقط، وسيتم حفظها تلقائيًا بصيغة JSON.</p>
        </div>
        <input type="hidden" name="nutrition" data-nutrition-json value="{{ old('nutrition', $nutrition ? json_encode($nutrition) : '') }}">
        <div class="nutrition-grid">
            @foreach($nutritionFields as $key => $label)
                <label class="form-field">
                    <span>{{ $label }}</span>
                    <input data-nutrition-key="{{ $key }}" type="number" min="0" step="0.01" value="{{ $nutrition[$key] ?? '' }}">
                </label>
            @endforeach
        </div>
    </section>

    <section class="json-editor-card full" data-ingredients-editor>
        <div class="json-editor-header">
            <h3>المكونات</h3>
            <p>أضف أو عدّل المكونات كوسوم سهلة القراءة، وسيتم حفظها كمصفوفة JSON.</p>
        </div>
        <input type="hidden" name="ingredients" data-ingredients-json value="{{ old('ingredients', $ingredients->isNotEmpty() ? $ingredients->toJson() : '') }}">
        <div class="ingredient-table" data-ingredient-tags>
            <div class="ingredient-table-head">
                <span>#</span>
                <span>المكون</span>
                <span>إجراء</span>
            </div>
            @foreach($ingredients as $ingredient)
                <span class="ingredient-chip" data-ingredient-chip>
                    <span class="ingredient-row-index">{{ $loop->iteration }}</span>
                    <input type="text" value="{{ $ingredient }}" data-ingredient-input aria-label="المكون">
                    <button type="button" data-remove-ingredient aria-label="حذف المكون">×</button>
                </span>
            @endforeach
        </div>
        <div class="ingredient-add-row">
            <input type="text" data-new-ingredient placeholder="أضف مكون جديد">
            <button class="crud-button ghost" type="button" data-add-ingredient>إضافة</button>
        </div>
    </section>

    <label class="form-field checkbox-field"><input name="is_recommended" type="checkbox" value="1" @checked(old('is_recommended', $meal?->is_recommended))> وجبة موصى بها</label>
    <label class="form-field checkbox-field"><input name="is_available" type="checkbox" value="1" @checked(old('is_available', $meal?->is_available ?? true))> متاح للطلب</label>
</div>

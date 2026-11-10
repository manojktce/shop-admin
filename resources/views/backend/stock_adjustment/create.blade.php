<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Adjustments" :crumbs="[['title' => 'Stock adjustments', 'link' => baseURL('stock-adjustments')], ['title' => 'New']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('stock-adjustments') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('stock-adjustments'))->open() }}
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-backend::form.label name="Product" for="product_id" required/>
                        <select name="product_id" id="product_id" class="form-control">
                            <option value="">Select</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-unit="{{ $product->baseUnit?->short_name }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->code }} - {{ $product->name }}{{ $product->is_active ? '' : ' (inactive)' }}</option>
                            @endforeach
                        </select>
                        <x-backend::form.error name="product_id" :errors="$errors"/>
                    </div>
                    <div class="col-md-3">
                        <x-backend::form.label name="Date" for="adjustment_date" required/>
                        <x-backend::form.input type="date" name="adjustment_date" id="adjustment_date" max="{{ now()->toDateString() }}" value="{{ old('adjustment_date', now()->toDateString()) }}"/>
                        <x-backend::form.error name="adjustment_date" :errors="$errors"/>
                    </div>
                    <div class="col-md-3">
                        <x-backend::form.label name="Quantity change (base unit)" for="base_qty" required/>
                        <div class="input-group">
                            <x-backend::form.input type="number" name="base_qty" id="base_qty" step="0.001" value="{{ old('base_qty') }}"/>
                            <span class="input-group-text" id="unit-label"></span>
                        </div>
                        <x-backend::form.error name="base_qty" :errors="$errors"/>
                        <p class="tagline">Use + to add stock and - to reduce it.</p>
                    </div>
                    <div class="col-md-4">
                        <x-backend::form.label name="Reason" for="reason" required/>
                        <x-backend::form.select name="reason" id="reason" placeholder="Select" value="{{ old('reason') }}" :options="\App\Models\StockAdjustment::REASONS"/>
                        <x-backend::form.error name="reason" :errors="$errors"/>
                    </div>
                    <div class="col-md-8">
                        <x-backend::form.label name="Notes (required for Other)" for="notes"/>
                        <x-backend::form.input name="notes" id="notes" maxlength="255" value="{{ old('notes') }}"/>
                        <x-backend::form.error name="notes" :errors="$errors"/>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            {{ html()->form()->close() }}
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const show = () => $('#unit-label').text($('#product_id option:selected').data('unit') || '');
                $('#product_id').on('change', show);
                show();
            });
        </script>
    @endpush
</x-backend.layouts.full>

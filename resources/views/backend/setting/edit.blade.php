<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Shop settings" :crumbs="[['title' => 'Shop settings']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL('settings'))->acceptsFiles()->open() }}
            <div class="row g-3">
                <h5 class="mb-0">Shop details (printed on invoices)</h5>
                @foreach (['shop_name' => 'Shop name', 'phone' => 'Phone', 'gstin' => 'GSTIN', 'address' => 'Address'] as $key => $label)
                    <div class="col-md-6">
                        <x-backend::form.label name="{{ $label }}" for="{{ $key }}" :required="$key === 'shop_name'"/>
                        <x-backend::form.input name="{{ $key }}" id="{{ $key }}" value="{{ old($key, $settings[$key]) }}"/>
                        <x-backend::form.error name="{{ $key }}" :errors="$errors"/>
                    </div>
                @endforeach
                <div class="col-md-6">
                    <x-backend::form.label name="Logo" for="logo"/>
                    <input type="file" name="logo" id="logo" class="form-control" accept=".jpg,.jpeg,.png">
                    @if ($settings['logo'])
                        <img src="{{ url('storage/' . $settings['logo']) }}" alt="Shop logo" class="mt-2" style="max-height:60px">
                    @endif
                    <x-backend::form.error name="logo" :errors="$errors"/>
                </div>

                <h5 class="mb-0 mt-4">Numbering</h5>
                <p class="text-muted mb-0">Numbers continue automatically, e.g. {{ $settings['invoice_prefix'] }}{{ str_pad($settings['invoice_next_no'], 4, '0', STR_PAD_LEFT) }} is the next invoice.</p>
                @foreach (['invoice' => 'Invoice', 'receipt' => 'Receipt', 'stock_entry' => 'Stock in'] as $key => $label)
                    <div class="col-md-3">
                        <x-backend::form.label name="{{ $label }} prefix" for="{{ $key }}_prefix" required/>
                        <x-backend::form.input name="{{ $key }}_prefix" id="{{ $key }}_prefix" value="{{ old($key.'_prefix', $settings[$key.'_prefix']) }}"/>
                        <x-backend::form.error name="{{ $key }}_prefix" :errors="$errors"/>
                    </div>
                    <div class="col-md-3">
                        <x-backend::form.label name="Next {{ strtolower($label) }} number" for="{{ $key }}_next_no" required/>
                        <x-backend::form.input type="number" min="1" name="{{ $key }}_next_no" id="{{ $key }}_next_no" value="{{ old($key.'_next_no', $settings[$key.'_next_no']) }}"/>
                        <x-backend::form.error name="{{ $key }}_next_no" :errors="$errors"/>
                    </div>
                @endforeach

                <h5 class="mb-0 mt-4">Billing</h5>
                <div class="col-md-4">
                    <x-backend::form.label name="Invoice print size" for="print_size" required/>
                    <x-backend::form.select name="print_size" id="print_size" value="{{ old('print_size', $settings['print_size']) }}" :options="$printSizes"/>
                    <x-backend::form.error name="print_size" :errors="$errors"/>
                </div>
                <div class="col-md-4">
                    <x-backend::form.label name="Bill rounding" for="bill_rounding" required/>
                    <x-backend::form.select name="bill_rounding" id="bill_rounding" value="{{ old('bill_rounding', $settings['bill_rounding']) }}" :options="$roundings"/>
                    <x-backend::form.error name="bill_rounding" :errors="$errors"/>
                </div>
                <div class="col-md-4">
                    <x-backend::form.label name="Allow stock below zero" for="allow_negative_stock"/>
                    <x-backend::form.switch name="allow_negative_stock" id="allow_negative_stock" value="1" checked="{{ old('allow_negative_stock', $settings['allow_negative_stock']) }}"/>
                    <p class="tagline">Lets billing continue when stock in was not entered.</p>
                </div>
                <p class="text-muted mb-0">Selling prices include GST.</p>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </div>
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>

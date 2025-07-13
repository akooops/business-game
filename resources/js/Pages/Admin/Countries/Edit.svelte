<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Props from controller
    export let country;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Countries',
            url: route('admin.countries.index'),
            active: false
        },
        {
            title: 'Edit',
            url: route('admin.countries.edit', { country: country.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Country';

    // Form data - Initialize with existing country data
    let form = {
        name: country.name || '',
        code: country.code || '',
        customs_duties_rate: country.customs_duties_rate || 0.25,
        tva_rate: country.tva_rate || 0.19,
        insurance_rate: country.insurance_rate || 0.005,
        freight_cost: country.freight_cost || 0,
        port_handling_fee: country.port_handling_fee || 20000,
        allows_imports: country.allows_imports || true,
        min_shipping_cost: country.min_shipping_cost || 0,
        max_shipping_cost: country.max_shipping_cost || 0,
        avg_shipping_cost: country.avg_shipping_cost || 0,
        min_shipping_time_days: country.min_shipping_time_days || 1,
        avg_shipping_time_days: country.avg_shipping_time_days || 3,
        max_shipping_time_days: country.max_shipping_time_days || 7,
        file: null
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File preview
    let filePreview = null;
    let existingFlagUrl = country.flag_url;

    // Handle file input change
    function handleFileChange(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            form.file = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle imports toggle
    function handleImportsToggle() {
        form.allows_imports = !form.allows_imports;
    }

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                if (key === 'file' && form.file) {
                    formData.append(key, form.file);
                } else if (key === 'allows_imports') {
                    formData.append(key, form[key] ? '1' : '0');
                } else if (key !== 'file') {
                    formData.append(key, form[key]);
                }
            }
        });

        router.post(route('admin.countries.update', { country: country.id }), formData, {
            onError: (err) => {
                errors = err;
                loading = false;
            },
            onFinish: () => {
                loading = false;
            }
        });
    }

    // Initialize components after mount
    onMount(async () => {
        await tick();
    });
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Country Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Country</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update {country.name} information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.countries.show', { country: country.id })}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-eye text-base"></i>
                        View Country
                    </a>
                    <a href="{route('admin.countries.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Countries
                    </a>
                </div>
            </div>

            <!-- Form -->
            <form on:submit|preventDefault={handleSubmit} class="grid gap-5 lg:gap-7.5">
                <!-- Basic Information Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Basic Information</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Country Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Country Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter country name"
                                    bind:value={form.name}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>

                            <!-- Country Code -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="code">
                                    Country Code <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="code"
                                    type="text"
                                    class="kt-input {errors.code ? 'kt-input-error' : ''}"
                                    placeholder="Enter country code (e.g., USA, CHN)"
                                    maxlength="3"
                                    bind:value={form.code}
                                />
                                {#if errors.code}
                                    <p class="text-sm text-destructive">{errors.code}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">3 characters maximum</p>
                            </div>

                            <!-- Allows Imports -->
                            <div class="flex items-center gap-2">
                                <input 
                                    class="kt-switch" 
                                    type="checkbox" 
                                    id="allows_imports" 
                                    checked={form.allows_imports}
                                    on:change={(e) => {
                                        form.allows_imports = e.target.checked;
                                    }}
                                />
                                {#if errors.allows_imports}
                                    <p class="text-sm text-destructive">{errors.allows_imports}</p>
                                {/if}
                                <label class="kt-label" for="allows_imports">
                                    Allow imports from this country
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Rates Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Tax Rates</h4>
                        <p class="text-sm text-secondary-foreground">Enter rates as decimals (e.g., 0.25 for 25%)</p>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <!-- Customs Duties Rate -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="customs_duties_rate">
                                    Customs Duties Rate <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="customs_duties_rate"
                                    type="number"
                                    class="kt-input {errors.customs_duties_rate ? 'kt-input-error' : ''}"
                                    placeholder="0.25"
                                    step="0.001"
                                    min="0"
                                    max="1"
                                    bind:value={form.customs_duties_rate}
                                />
                                {#if errors.customs_duties_rate}
                                    <p class="text-sm text-destructive">{errors.customs_duties_rate}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">
                                    Current: {(form.customs_duties_rate * 100).toFixed(1)}%
                                </p>
                            </div>

                            <!-- TVA Rate -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="tva_rate">
                                    TVA Rate <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="tva_rate"
                                    type="number"
                                    class="kt-input {errors.tva_rate ? 'kt-input-error' : ''}"
                                    placeholder="0.19"
                                    step="0.001"
                                    min="0"
                                    max="1"
                                    bind:value={form.tva_rate}
                                />
                                {#if errors.tva_rate}
                                    <p class="text-sm text-destructive">{errors.tva_rate}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">
                                    Current: {(form.tva_rate * 100).toFixed(1)}%
                                </p>
                            </div>

                            <!-- Insurance Rate -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="insurance_rate">
                                    Insurance Rate <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="insurance_rate"
                                    type="number"
                                    class="kt-input {errors.insurance_rate ? 'kt-input-error' : ''}"
                                    placeholder="0.005"
                                    step="0.001"
                                    min="0"
                                    max="1"
                                    bind:value={form.insurance_rate}
                                />
                                {#if errors.insurance_rate}
                                    <p class="text-sm text-destructive">{errors.insurance_rate}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">
                                    Current: {(form.insurance_rate * 100).toFixed(1)}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Costs Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping & Handling Costs</h4>
                        <p class="text-sm text-secondary-foreground">Enter costs in USD</p>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <!-- Freight Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="freight_cost">
                                    Freight Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="freight_cost"
                                    type="number"
                                    class="kt-input {errors.freight_cost ? 'kt-input-error' : ''}"
                                    placeholder="0"
                                    step="0.01"
                                    min="0"
                                    bind:value={form.freight_cost}
                                />
                                {#if errors.freight_cost}
                                    <p class="text-sm text-destructive">{errors.freight_cost}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Base freight cost per shipment</p>
                            </div>

                            <!-- Port Handling Fee -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="port_handling_fee">
                                    Port Handling Fee <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="port_handling_fee"
                                    type="number"
                                    class="kt-input {errors.port_handling_fee ? 'kt-input-error' : ''}"
                                    placeholder="20000"
                                    step="0.01"
                                    min="0"
                                    bind:value={form.port_handling_fee}
                                />
                                {#if errors.port_handling_fee}
                                    <p class="text-sm text-destructive">{errors.port_handling_fee}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Port handling charges</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Cost Ranges Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping Cost Ranges</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <!-- Minimum Shipping Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="min_shipping_cost">
                                    Minimum Shipping Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="min_shipping_cost"
                                    type="number"
                                    class="kt-input {errors.min_shipping_cost ? 'kt-input-error' : ''}"
                                    placeholder="0"
                                    step="0.001"
                                    min="0"
                                    bind:value={form.min_shipping_cost}
                                />
                                {#if errors.min_shipping_cost}
                                    <p class="text-sm text-destructive">{errors.min_shipping_cost}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Best case shipping cost</p>
                            </div>

                            <!-- Average Shipping Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="avg_shipping_cost">
                                    Average Shipping Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="avg_shipping_cost"
                                    type="number"
                                    class="kt-input {errors.avg_shipping_cost ? 'kt-input-error' : ''}"
                                    placeholder="0"
                                    step="0.001"
                                    min="0"
                                    bind:value={form.avg_shipping_cost}
                                />
                                {#if errors.avg_shipping_cost}
                                    <p class="text-sm text-destructive">{errors.avg_shipping_cost}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Typical shipping cost</p>
                            </div>

                            <!-- Maximum Shipping Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="max_shipping_cost">
                                    Maximum Shipping Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="max_shipping_cost"
                                    type="number"
                                    class="kt-input {errors.max_shipping_cost ? 'kt-input-error' : ''}"
                                    placeholder="0"
                                    step="0.001"
                                    min="0"
                                    bind:value={form.max_shipping_cost}
                                />
                                {#if errors.max_shipping_cost}
                                    <p class="text-sm text-destructive">{errors.max_shipping_cost}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Worst case shipping cost</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Time Ranges Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping Time Ranges</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <!-- Minimum Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="min_shipping_time_days">
                                    Minimum Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="min_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.min_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="1"
                                    min="1"
                                    bind:value={form.min_shipping_time_days}
                                />
                                {#if errors.min_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.min_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Express delivery time (days)</p>
                            </div>

                            <!-- Average Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="avg_shipping_time_days">
                                    Average Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="avg_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.avg_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="3"
                                    min="1"
                                    bind:value={form.avg_shipping_time_days}
                                />
                                {#if errors.avg_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.avg_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Standard delivery time (days)</p>
                            </div>

                            <!-- Maximum Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="max_shipping_time_days">
                                    Maximum Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="max_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.max_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="7"
                                    min="1"
                                    bind:value={form.max_shipping_time_days}
                                />
                                {#if errors.max_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.max_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Delivery time (days)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flag Upload Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Country Flag</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Flag -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono">Current Flag</label>
                                <div class="w-24 h-16 border rounded-lg overflow-hidden">
                                    <img src={existingFlagUrl} alt="Current Flag" class="w-full h-full object-cover" />
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Update Flag Image
                                </label>
                                <input
                                    id="file"
                                    type="file"
                                    class="kt-input {errors.file ? 'kt-input-error' : ''}"
                                    accept="image/jpeg,image/jpg,image/png"
                                    on:change={handleFileChange}
                                />
                                {#if errors.file}
                                    <p class="text-sm text-destructive">{errors.file}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">
                                    Upload a new flag image (JPEG, JPG, PNG, max 2MB). Leave empty to keep current flag.
                                </p>
                            </div>

                            <!-- File Preview -->
                            {#if filePreview}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">New Flag Preview</label>
                                    <div class="w-24 h-16 border rounded-lg overflow-hidden">
                                        <img src={filePreview} alt="Flag Preview" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3">
                    <a href="{route('admin.countries.show', { country: country.id })}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Country
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
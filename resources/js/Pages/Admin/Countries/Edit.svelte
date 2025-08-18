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
        customs_duties_rate: country.customs_duties_rate || 0.25,
        allows_imports: country.allows_imports,
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                if (key === 'allows_imports') {
                    formData.append(key, form[key] ? '1' : '0');
                } else {
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
                        <div class="grid gap-4">
                            <!-- Customs Duties Rate -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="customs_duties_rate">
                                    Customs Duties Rate <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="customs_duties_rate"
                                    type="number"
                                    class="kt-input {errors.customs_duties_rate ? 'kt-input-error' : ''}"
                                    placeholder="Enter customs duties rate"
                                    step="0.001"
                                    min="0"
                                    max="1"
                                    bind:value={form.customs_duties_rate}
                                />
                                {#if errors.customs_duties_rate}
                                    <p class="text-sm text-destructive">{errors.customs_duties_rate}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">
                                    Current: {(form.customs_duties_rate * 100)}%
                                </p>
                            </div>
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
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Props from controller
    export let wilaya;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Wilayas',
            url: route('admin.wilayas.index'),
            active: false
        },
        {
            title: 'Edit',
            url: route('admin.wilayas.edit', { wilaya: wilaya.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Wilaya';

    // Form data - Initialize with existing wilaya data
    let form = {
        name: wilaya.name || '',
        min_shipping_cost: wilaya.min_shipping_cost || 0,
        max_shipping_cost: wilaya.max_shipping_cost || 0,
        avg_shipping_cost: wilaya.avg_shipping_cost || 0,
        min_shipping_time_days: wilaya.min_shipping_time_days || 1,
        avg_shipping_time_days: wilaya.avg_shipping_time_days || 3,
        max_shipping_time_days: wilaya.max_shipping_time_days || 7
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
                formData.append(key, form[key]);
            }
        });

        router.post(route('admin.wilayas.update', { wilaya: wilaya.id }), formData, {
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
            <!-- Wilaya Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Wilaya</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update {wilaya.name} information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.wilayas.show', { wilaya: wilaya.id })}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-eye text-base"></i>
                        View Wilaya
                    </a>
                    <a href="{route('admin.wilayas.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Wilayas
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
                            <!-- Wilaya Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Wilaya Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter wilaya name (e.g., Algiers, Oran)"
                                    bind:value={form.name}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Costs Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping Costs</h4>
                        <p class="text-sm text-secondary-foreground">
                            Update the shipping cost ranges for deliveries to this wilaya
                        </p>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <!-- Min Shipping Cost -->
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
                                <p class="text-sm text-secondary-foreground">Lowest possible shipping cost (DZD)</p>
                            </div>

                            <!-- Avg Shipping Cost -->
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
                                <p class="text-sm text-secondary-foreground">Typical shipping cost (DZD)</p>
                            </div>

                            <!-- Max Shipping Cost -->
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
                                <p class="text-sm text-secondary-foreground">Highest possible shipping cost (DZD)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Times Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping Times</h4>
                        <p class="text-sm text-secondary-foreground">
                            Update the delivery time ranges for shipments to this wilaya
                        </p>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <!-- Min Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="min_shipping_time_days">
                                    Minimum Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="min_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.min_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="1"
                                    step="1"
                                    min="0"
                                    bind:value={form.min_shipping_time_days}
                                />
                                {#if errors.min_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.min_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Fastest delivery time (days)</p>
                            </div>

                            <!-- Avg Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="avg_shipping_time_days">
                                    Average Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="avg_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.avg_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="3"
                                    step="1"
                                    min="0"
                                    bind:value={form.avg_shipping_time_days}
                                />
                                {#if errors.avg_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.avg_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Typical delivery time (days)</p>
                            </div>

                            <!-- Max Shipping Time -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="max_shipping_time_days">
                                    Maximum Shipping Time <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="max_shipping_time_days"
                                    type="number"
                                    class="kt-input {errors.max_shipping_time_days ? 'kt-input-error' : ''}"
                                    placeholder="7"
                                    step="1"
                                    min="0"
                                    bind:value={form.max_shipping_time_days}
                                />
                                {#if errors.max_shipping_time_days}
                                    <p class="text-sm text-destructive">{errors.max_shipping_time_days}</p>
                                {/if}
                                <p class="text-sm text-secondary-foreground">Longest delivery time (days)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3">
                    <a href="{route('admin.wilayas.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Wilaya
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
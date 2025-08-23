<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';

    // Props passed from controller
    export let advertiser;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Advertisers',
            url: route('admin.advertisers.index'),
            active: false
        },
        {
            title: advertiser.name,
            url: route('admin.advertisers.show', { advertiser: advertiser.id }),
            active: false
        },
        {
            title: 'Edit',
            url: route('admin.advertisers.edit', { advertiser: advertiser.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Advertiser';

    // Form data - pre-populate with existing data
    let formData = {
        name: advertiser.name || '',
        min_price: advertiser.min_price || '',
        max_price: advertiser.max_price || '',
        min_market_impact_percentage: advertiser.min_market_impact_percentage || '',
        max_market_impact_percentage: advertiser.max_market_impact_percentage || '',
        duration_days: advertiser.duration_days || '',
        file: null
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File preview
    let filePreview = null;

    // Handle file input change
    function handleFileChange(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            formData.file = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle form submission
    async function handleSubmit() {
        if (loading) return;

        loading = true;
        errors = {};

        try {
            formData._method = 'PATCH';

            router.post(route('admin.advertisers.update', { advertiser: advertiser.id }), formData, {
                onError: (err) => {
                    errors = err;
                    loading = false;
                },
                onSuccess: () => {
                    loading = false;
                }
            });
        } catch (error) {
            console.error('Error updating advertiser:', error);
            loading = false;
        }
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Advertiser</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update advertiser information and lending terms
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.advertisers.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-black-left text-base"></i>
                        Back to Advertisers
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
                            <!-- Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Advertiser Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="name"
                                    type="text" 
                                    bind:value={formData.name}
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter advertiser name"
                                    required
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advertiser Logo Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Advertiser Logo</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Logo -->
                            {#if advertiser.logo}
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-mono">Current Logo</span>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={advertiser.logo_url} 
                                            alt="Current advertiser logo" 
                                            class="w-32 h-32 object-cover rounded-lg border" 
                                        />
                                    </div>
                                </div>
                            {/if}

                            <!-- File Upload Section -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Upload New Logo <span class="text-secondary-foreground">(Keep empty if you don't want to change it)</span>
                                </label>
                                <input
                                    id="file"
                                    type="file"
                                    class="kt-input"
                                    accept="image/*"
                                    on:change={handleFileChange}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Supported formats: JPG, JPEG, PNG, WebP
                                </p>
                                {#if filePreview}
                                    <div class="mt-2">
                                        <img src={filePreview} alt="Preview" class="w-32 h-32 object-cover rounded-lg border" />
                                    </div>
                                {/if}
                                {#if errors.file}
                                    <p class="text-sm text-destructive">{errors.file}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advertiser Terms Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Advertiser Terms</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Price Range -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_price">
                                        Minimum Price (DZD) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="min_price"
                                        type="number" 
                                        bind:value={formData.min_price}
                                        class="kt-input {errors.min_price ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum price"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.min_price}
                                        <p class="text-sm text-destructive">{errors.min_price}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_price">
                                        Maximum Price (DZD) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                            id="max_price"
                                        type="number" 
                                        bind:value={formData.max_price}
                                        class="kt-input {errors.max_price ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum price"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.max_price}
                                        <p class="text-sm text-destructive">{errors.max_price}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Market Impact -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_market_impact_percentage">
                                        Minimum Market Impact (%)
                                    </label>
                                    <input 
                                        id="min_market_impact_percentage"
                                        type="number" 
                                        bind:value={formData.min_market_impact_percentage}
                                        class="kt-input {errors.min_market_impact_percentage ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum market impact"
                                        min="0"
                                        max="1"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.min_market_impact_percentage}
                                        <p class="text-sm text-destructive">{errors.min_market_impact_percentage}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_market_impact_percentage">
                                        Maximum Market Impact (%)
                                    </label>
                                    <input 
                                        id="max_market_impact_percentage"
                                        type="number" 
                                        bind:value={formData.max_market_impact_percentage}
                                        class="kt-input {errors.max_market_impact_percentage ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum market impact"
                                        min="0"
                                        max="1"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.max_market_impact_percentage}
                                        <p class="text-sm text-destructive">{errors.max_market_impact_percentage}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm   font-medium text-mono" for="duration_days">
                                    Advertiser Duration (days) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="duration_days"
                                    type="number" 
                                    bind:value={formData.duration_days}
                                    class="kt-input {errors.duration_days ? 'kt-input-error' : ''}"
                                    placeholder="Enter advertiser duration in days"
                                    min="0"
                                    step="1"
                                    required
                                />
                                    {#if errors.duration_days}
                                    <p class="text-sm text-destructive">{errors.duration_days}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.advertisers.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Advertiser
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout>
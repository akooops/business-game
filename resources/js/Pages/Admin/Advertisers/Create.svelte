<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Advertisers',
            url: route('admin.advertisers.index'),
            active: false
        },
        {
            title: 'Create',
            url: route('admin.advertisers.create'),
            active: true
        }
    ];
    
    const pageTitle = 'Create Advertiser';

    // Form data
    let formData = {
        name: '',
        min_price: '',
        max_price: '',
        min_market_impact_percentage: '',
        max_market_impact_percentage: '',
        duration_days: '',
        file: null
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File input reference
    let fileInput;

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
            router.post(route('admin.advertisers.store'), formData, {
                onError: (err) => {
                    errors = err;
                    loading = false;
                },
                onSuccess: () => {
                    loading = false;
                }
            });
        } catch (error) {
            console.error('Error submitting form:', error);
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
                    <h1 class="text-2xl font-bold text-mono">Create Advertiser</h1>
                    <p class="text-sm text-secondary-foreground">
                        Add a new advertiser with lending services to the business game
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
                            <!-- File Upload Section -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Upload Logo
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
                                        step="0.001"
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
                                        Minimum Market Impact (%) <span class="text-destructive">*</span>
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
                                        Maximum Market Impact (%) <span class="text-destructive">*</span>
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
                                <label class="text-sm font-medium text-mono" for="duration_days">
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
                            Creating...
                        {:else}
                            <i class="ki-filled ki-plus text-base"></i>
                            Create Advertiser
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout>
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Countries',
            url: route('admin.countries.index'),
            active: false
        },
        {
            title: 'Create',
            url: route('admin.countries.create'),
            active: true
        }
    ];
    
    const pageTitle = 'Create Country';

    // Form data
    let form = {
        name: '',
        customs_duties_rate: 0.25,
        allows_imports: true,
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

        router.post(route('admin.countries.store'), formData, {
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
                    <h1 class="text-2xl font-bold text-mono">Create New Country</h1>
                    <p class="text-sm text-secondary-foreground">
                        Add a new country to your import/export system
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
                            <!-- File Upload -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Flag Image <span class="text-destructive">*</span>
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
                                    Upload a flag image (JPEG, JPG, PNG, max 2MB)
                                </p>
                            </div>

                            <!-- File Preview -->
                            {#if filePreview}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Preview</label>
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
                    <a href="{route('admin.countries.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Creating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Create Country
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Props from the server
    export let technology;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Technologies',
            url: route('admin.technologies.index'),
            active: false
        },
        {
            title: technology.name || 'Technology Details',
            url: route('admin.technologies.edit', { technology: technology.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Technology';

    // Form data - pre-populate with product data
    let form = {
        name: technology.name || '',
        description: technology.description || '',
        level: technology.level || '',
        research_cost: technology.research_cost || '',
        research_time_days: technology.research_time_days || '',
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

    // Handle expiration toggle
    function handleExpirationToggle() {
        form.has_expiration = !form.has_expiration;
        if (!form.has_expiration) {
            form.shelf_life_days = '';
        }
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
                } else if (key === 'has_expiration') {
                    formData.append(key, form[key] ? '1' : '0');
                } else if (key !== 'file') {
                    formData.append(key, form[key]);
                }
            }
        });

        // Add _method for PATCH request
        formData.append('_method', 'PATCH');

        router.post(route('admin.technologies.update', { technology: technology.id }), formData, {
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
            <!-- Technology Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Technology</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update technology information and properties
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.technologies.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Technologies
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
                            <!-- Technology Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Technology Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter technology name"
                                    bind:value={form.name}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>

                            <!-- Description -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="description">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    class="kt-textarea {errors.description ? 'kt-input-error' : ''}"
                                    placeholder="Enter technology description"
                                    rows="3"
                                    bind:value={form.description}
                                ></textarea>
                                {#if errors.description}
                                    <p class="text-sm text-destructive">{errors.description}</p>
                                {/if}
                            </div>

                            <!-- Technology Level -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="level">
                                    Technology Level <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="level"
                                    type="number"
                                    class="kt-input {errors.level ? 'kt-input-error' : ''}"
                                    placeholder="Enter technology level"
                                    bind:value={form.level}
                                />
                                {#if errors.level}
                                    <p class="text-sm text-destructive">{errors.level}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technology Research Requirements Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Technology Research Requirements</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Research Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="research_cost">
                                    Research Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="research_cost"
                                    type="number"
                                    min="0"
                                    step="0.001"
                                    class="kt-input {errors.research_cost ? 'kt-input-error' : ''}"
                                    placeholder="Enter research cost"
                                    bind:value={form.research_cost}
                                />
                                {#if errors.research_cost}
                                    <p class="text-sm text-destructive">{errors.research_cost}</p>
                                {/if}
                            </div>

                            <!-- Research Time (Days) -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="research_time_days">
                                    Research Time (Days) <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="research_time_days"
                                    type="number"
                                    min="0"
                                    class="kt-input {errors.research_time_days ? 'kt-input-error' : ''}"
                                    placeholder="Enter research time (days)"
                                    bind:value={form.research_time_days}
                                />
                                {#if errors.research_time_days}
                                    <p class="text-sm text-destructive">{errors.research_time_days}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technology Image Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Technology Image</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Image -->
                            {#if technology.image}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Current Image</label>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={technology.image_url} 
                                            alt="Current technology image" 
                                            class="w-32 h-32 object-cover rounded-lg border" 
                                        />
                                    </div>
                                </div>
                            {/if}

                            <!-- File Upload Section -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Upload New Image <span class="text-secondary-foreground">(Keep empty if you don't want to change it)</span>
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

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.technologies.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="kt-btn kt-btn-primary"
                        disabled={loading}
                    >
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Technology
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Props from the server
    export let company;

    // Define breadcrumbs for this company
    const breadcrumbs = [
        {
            title: 'Companies',
            url: route('admin.companies.index'),
            active: false
        },
        {
            title: `${company.user.firstname} ${company.user.lastname}` || 'Company Details',
            url: route('admin.companies.edit', { company: company.id }),
            active: true
        }
    ];
    
    const pageTitle = `Edit Company`;

    // Form data - pre-populate with company data
    let form = {
        firstname: company.user.firstname || '',
        lastname: company.user.lastname || '',
        username: company.user.username || '',
        email: company.user.email || '',
        password: '',
        file: null,
        funds: company.funds || 0,
        carbon_footprint: company.carbon_footprint || 0,
        research_level: company.research_level || 0,
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

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                if (key === 'file' && form.file) {
                    formData.append(key, form.file);
                } else if (key !== 'file') {
                    formData.append(key, form[key]);
                }
            }
        });

        // Add _method for PATCH request
        formData.append('_method', 'PATCH');

        router.post(route('admin.companies.update', { company: company.id }), formData, {
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
    <title>Business game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Company Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Company</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update company information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.companies.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Companies
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
                            <!-- First Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="firstname">
                                    First Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="firstname"
                                    type="text"
                                    class="kt-input {errors.firstname ? 'kt-input-error' : ''}"
                                    placeholder="Enter first name"
                                    bind:value={form.firstname}
                                />
                                {#if errors.firstname}
                                    <p class="text-sm text-destructive">{errors.firstname}</p>
                                {/if}
                            </div>

                            <!-- Last Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="lastname">
                                    Last Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="lastname"
                                    type="text"
                                    class="kt-input {errors.lastname ? 'kt-input-error' : ''}"
                                    placeholder="Enter last name"
                                    bind:value={form.lastname}
                                />
                                {#if errors.lastname}
                                    <p class="text-sm text-destructive">{errors.lastname}</p>
                                {/if}
                            </div>

                            <!-- Username -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="username">
                                    Username <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="username"
                                    type="text"
                                    class="kt-input {errors.username ? 'kt-input-error' : ''}"
                                    placeholder="Enter username"
                                    bind:value={form.username}
                                />
                                {#if errors.username}
                                    <p class="text-sm text-destructive">{errors.username}</p>
                                {/if}
                            </div>

                            <!-- Email -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="email">
                                    Email <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    class="kt-input {errors.email ? 'kt-input-error' : ''}"
                                    placeholder="Enter email address"
                                    bind:value={form.email}
                                />
                                {#if errors.email}
                                    <p class="text-sm text-destructive">{errors.email}</p>
                                {/if}
                            </div>

                            <!-- Password -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="password">
                                    Password <span class="text-secondary-foreground">(Keep empty if you don't want to change it)</span>
                                </label>
                                <div class="relative max-w-72" data-kt-toggle-password="true">
                                    <input 
                                        id="password"
                                        type="password" 
                                        class="kt-input pe-10 {errors.password ? 'kt-input-error' : ''}" 
                                        placeholder="Enter new password"
                                        bind:value={form.password}
                                    />
                                    <button
                                        class="kt-btn kt-btn-icon kt-btn-ghost size-6 absolute end-2 top-1/2 -translate-y-1/2"
                                        data-kt-toggle-password-trigger="true"
                                        type="button"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-eye kt-toggle-password-active:hidden"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                            ></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-eye-off hidden kt-toggle-password-active:block"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"
                                            ></path>
                                            <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"></path>
                                            <path
                                                d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"
                                            ></path>
                                            <path d="m2 2 20 20"></path>
                                        </svg>
                                    </button>
                                </div>
                                {#if errors.password}
                                    <p class="text-sm text-destructive">{errors.password}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Information Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Company Information</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Funds -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="funds">
                                    Funds <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="funds"
                                    type="number"
                                    class="kt-input {errors.funds ? 'kt-input-error' : ''}"
                                    step="0.001"
                                    placeholder="Enter funds"
                                    bind:value={form.funds}
                                />
                                {#if errors.funds}
                                    <p class="text-sm text-destructive">{errors.funds}</p>
                                {/if}
                            </div>

                            <!-- Carbon Footprint -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="carbon_footprint">
                                    Carbon Footprint <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="carbon_footprint"
                                    type="number"
                                    class="kt-input {errors.carbon_footprint ? 'kt-input-error' : ''}"
                                    placeholder="Enter carbon footprint"
                                    bind:value={form.carbon_footprint}
                                />
                                {#if errors.carbon_footprint}
                                    <p class="text-sm text-destructive">{errors.carbon_footprint}</p>
                                {/if}
                            </div>

                            <!-- Research Level -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="research_level">
                                    Research Level <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="research_level"
                                    type="number"
                                    class="kt-input {errors.research_level ? 'kt-input-error' : ''}"
                                    placeholder="Enter research level"
                                    bind:value={form.research_level}
                                />
                                {#if errors.research_level}
                                    <p class="text-sm text-destructive">{errors.research_level}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Picture Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Profile Picture</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Avatar -->
                            {#if company.user.avatarUrl}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Current Avatar</label>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={company.user.avatarUrl} 
                                            alt="Current avatar" 
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
                                    Supported formats: JPG, JPEG, PNG
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
                    <a href="{route('admin.companies.index')}" class="kt-btn kt-btn-outline">
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
                            Update Company
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
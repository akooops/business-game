<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Banks',
            url: route('admin.banks.index'),
            active: false
        },
        {
            title: 'Create',
            url: route('admin.banks.create'),
            active: true
        }
    ];
    
    const pageTitle = 'Create Bank';

    // Form data
    let formData = {
        name: '',
        loan_duration_months: '',
        loan_interest_rate: '',
        loan_max_amount: '',
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
            router.post(route('admin.banks.store'), formData, {
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
                    <h1 class="text-2xl font-bold text-mono">Create Bank</h1>
                    <p class="text-sm text-secondary-foreground">
                        Add a new bank with lending services to the business game
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.banks.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-black-left text-base"></i>
                        Back to Banks
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
                                    Bank Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="name"
                                    type="text" 
                                    bind:value={formData.name}
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter bank name"
                                    required
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Logo Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Bank Logo</h4>
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

                <!-- Loan Terms Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Loan Terms</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Loan Duration -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="loan_duration_months">
                                    Loan Duration (months) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="loan_duration_months"
                                    type="number" 
                                    bind:value={formData.loan_duration_months}
                                    class="kt-input {errors.loan_duration_months ? 'kt-input-error' : ''}"
                                    placeholder="Enter loan duration in months"
                                    min="1"
                                    step="1"
                                    required
                                />
                                {#if errors.loan_duration_months}
                                    <p class="text-sm text-destructive">{errors.loan_duration_months}</p>
                                {/if}
                            </div>

                            <!-- Interest Rate and Max Amount -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="loan_interest_rate">
                                        Interest Rate (%) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="loan_interest_rate"
                                        type="number" 
                                        bind:value={formData.loan_interest_rate}
                                        class="kt-input {errors.loan_interest_rate ? 'kt-input-error' : ''}"
                                        placeholder="Enter interest rate"
                                        min="0"
                                        max="1"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.loan_interest_rate}
                                        <p class="text-sm text-destructive">{errors.loan_interest_rate}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="loan_max_amount">
                                        Maximum Loan Amount (DZD) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="loan_max_amount"
                                        type="number" 
                                        bind:value={formData.loan_max_amount}
                                        class="kt-input {errors.loan_max_amount ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum loan amount"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.loan_max_amount}
                                        <p class="text-sm text-destructive">{errors.loan_max_amount}</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.banks.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Creating...
                        {:else}
                            <i class="ki-filled ki-plus text-base"></i>
                            Create Bank
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout>
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Employee Profiles',
            url: route('admin.employee-profiles.index'),
            active: false
        },
        {
            title: 'Create',
            url: route('admin.employee-profiles.create'),
            active: true
        }
    ];
    
    const pageTitle = 'Create Employee Profile';

    // Form data
    let form = {
        name: '',
        description: '',
        min_salary_month: '',
        max_salary_month: '',
        min_recruitment_cost: '',
        max_recruitment_cost: ''
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                formData.append(key, form[key]);
            }
        });

        router.post(route('admin.employee-profiles.store'), formData, {
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
            <!-- Employee Profile Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Create Employee Profile</h1>
                    <p class="text-sm text-secondary-foreground">
                        Define a new employee profile with skills, salary, and requirements
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.employee-profiles.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Employee Profiles
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
                            <!-- Profile Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Profile Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter profile name (e.g., Production Worker, Manager)"
                                    bind:value={form.name}
                                    required
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
                                    placeholder="Enter profile description and responsibilities"
                                    rows="3"
                                    bind:value={form.description}
                                ></textarea>
                                {#if errors.description}
                                    <p class="text-sm text-destructive">{errors.description}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Structure Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Salary Structure</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 md:grid-cols-3">
                            <!-- Minimum Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="min_salary_month">
                                    Minimum Monthly Salary <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="min_salary_month"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.min_salary_month ? 'kt-input-error' : ''}"
                                    placeholder="Enter minimum monthly salary"
                                    bind:value={form.min_salary_month}
                                    required
                                />
                                {#if errors.min_salary_month}
                                    <p class="text-sm text-destructive">{errors.min_salary_month}</p>
                                {/if}
                            </div>

                            <!-- Maximum Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="max_salary_month">
                                    Maximum Monthly Salary <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="max_salary_month"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.max_salary_month ? 'kt-input-error' : ''}"
                                    placeholder="Enter maximum monthly salary"
                                    bind:value={form.max_salary_month}
                                    required
                                />
                                {#if errors.max_salary_month}
                                    <p class="text-sm text-destructive">{errors.max_salary_month}</p>
                                {/if}
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-2">
                            Define the salary range for this employee profile. Average should be between min and max.
                        </p>
                    </div>
                </div>

                <!-- Recruitment Cost Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Recruitment Cost</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 md:grid-cols-3">
                            <!-- Minimum Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="min_recruitment_cost">
                                    Minimum Recruitment Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="min_recruitment_cost"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.min_recruitment_cost ? 'kt-input-error' : ''}"
                                    placeholder="Enter minimum recruitment cost"
                                    bind:value={form.min_recruitment_cost}
                                    required
                                />
                                {#if errors.min_recruitment_cost}
                                    <p class="text-sm text-destructive">{errors.min_recruitment_cost}</p>
                                {/if}
                            </div>

                            <!-- Maximum Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="max_recruitment_cost">
                                    Maximum Recruitment Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="max_recruitment_cost"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.max_recruitment_cost ? 'kt-input-error' : ''}"
                                    placeholder="Enter maximum recruitment cost"
                                    bind:value={form.max_recruitment_cost}
                                    required
                                />
                                {#if errors.max_recruitment_cost}
                                    <p class="text-sm text-destructive">{errors.max_recruitment_cost}</p>
                                {/if}
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-2">
                            Define the recruitment cost range for this employee profile. Average should be between min and max.
                        </p>
                    </div>
                </div>


                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.employee-profiles.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="kt-btn kt-btn-primary"
                        disabled={loading}
                    >
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Creating...
                        {:else}
                            <i class="ki-filled ki-plus text-base"></i>
                            Create Employee Profile
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
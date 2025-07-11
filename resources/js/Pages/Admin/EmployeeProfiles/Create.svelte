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

    // Recruitment difficulty options
    const difficultyOptions = [
        { value: 'very_easy', label: 'Very Easy' },
        { value: 'easy', label: 'Easy' },
        { value: 'medium', label: 'Medium' },
        { value: 'hard', label: 'Hard' },
        { value: 'very_hard', label: 'Very Hard' }
    ];

    // Form data
    let form = {
        name: '',
        description: '',
        skills: [],
        monthly_min_salary: '',
        monthly_avg_salary: '',
        monthly_max_salary: '',
        recruitment_difficulty: 'medium',
        recruitment_cost_per_employee: '',
        training_cost_per_employee: '',
        training_duration_days: ''
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Skills input
    let skillInput = '';

    // Add skill
    function addSkill() {
        if (skillInput.trim() && !form.skills.includes(skillInput.trim())) {
            form.skills = [...form.skills, skillInput.trim()];
            skillInput = '';
        }
    }

    // Remove skill
    function removeSkill(index) {
        form.skills = form.skills.filter((_, i) => i !== index);
    }

    // Handle skill input keypress
    function handleSkillKeypress(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addSkill();
        }
    }

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                if (key === 'skills') {
                    formData.append(key, JSON.stringify(form[key]));
                } else {
                    formData.append(key, form[key]);
                }
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
                    <h1 class="text-2xl font-bold text-mono">Create New Employee Profile</h1>
                    <p class="text-sm text-secondary-foreground">
                        Add a new employee profile to your simulation
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

                            <!-- Skills -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono">
                                    Skills & Competencies
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        class="kt-input flex-1"
                                        placeholder="Enter a skill and press Enter"
                                        bind:value={skillInput}
                                        on:keypress={handleSkillKeypress}
                                    />
                                    <button
                                        type="button"
                                        class="kt-btn kt-btn-primary"
                                        on:click={addSkill}
                                        disabled={!skillInput.trim()}
                                    >
                                        <i class="ki-filled ki-plus text-base"></i>
                                        Add
                                    </button>
                                </div>
                                {#if form.skills.length > 0}
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        {#each form.skills as skill, index}
                                            <span class="kt-badge kt-badge-outline kt-badge-sm">
                                                {skill}
                                                <button
                                                    type="button"
                                                    class="ml-1 text-red-500 hover:text-red-700"
                                                    on:click={() => removeSkill(index)}
                                                >
                                                    <i class="ki-filled ki-cross text-xs"></i>
                                                </button>
                                            </span>
                                        {/each}
                                    </div>
                                {/if}
                                {#if errors.skills}
                                    <p class="text-sm text-destructive">{errors.skills}</p>
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
                                <label class="text-sm font-medium text-mono" for="monthly_min_salary">
                                    Minimum Monthly Salary <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="monthly_min_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="kt-input {errors.monthly_min_salary ? 'kt-input-error' : ''}"
                                    placeholder="0.00"
                                    bind:value={form.monthly_min_salary}
                                />
                                {#if errors.monthly_min_salary}
                                    <p class="text-sm text-destructive">{errors.monthly_min_salary}</p>
                                {/if}
                            </div>

                            <!-- Average Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="monthly_avg_salary">
                                    Average Monthly Salary <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="monthly_avg_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="kt-input {errors.monthly_avg_salary ? 'kt-input-error' : ''}"
                                    placeholder="0.00"
                                    bind:value={form.monthly_avg_salary}
                                />
                                {#if errors.monthly_avg_salary}
                                    <p class="text-sm text-destructive">{errors.monthly_avg_salary}</p>
                                {/if}
                            </div>

                            <!-- Maximum Salary -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="monthly_max_salary">
                                    Maximum Monthly Salary <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="monthly_max_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="kt-input {errors.monthly_max_salary ? 'kt-input-error' : ''}"
                                    placeholder="0.00"
                                    bind:value={form.monthly_max_salary}
                                />
                                {#if errors.monthly_max_salary}
                                    <p class="text-sm text-destructive">{errors.monthly_max_salary}</p>
                                {/if}
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-2">
                            Define the salary range for this employee profile. Average should be between min and max.
                        </p>
                    </div>
                </div>

                <!-- Recruitment Settings Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Recruitment Settings</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Recruitment Difficulty -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="recruitment_difficulty">
                                    Recruitment Difficulty <span class="text-destructive">*</span>
                                </label>
                                <select
                                    id="recruitment_difficulty"
                                    class="kt-select {errors.recruitment_difficulty ? 'kt-select-error' : ''}"
                                    bind:value={form.recruitment_difficulty}
                                >
                                    {#each difficultyOptions as option}
                                        <option value={option.value}>{option.label}</option>
                                    {/each}
                                </select>
                                <p class="text-xs text-secondary-foreground">
                                    How difficult it is to find and recruit employees for this profile
                                </p>
                                {#if errors.recruitment_difficulty}
                                    <p class="text-sm text-destructive">{errors.recruitment_difficulty}</p>
                                {/if}
                            </div>

                            <!-- Recruitment Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="recruitment_cost_per_employee">
                                    Recruitment Cost per Employee <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="recruitment_cost_per_employee"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="kt-input {errors.recruitment_cost_per_employee ? 'kt-input-error' : ''}"
                                    placeholder="0.00"
                                    bind:value={form.recruitment_cost_per_employee}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Cost to recruit one employee (advertising, interviews, etc.)
                                </p>
                                {#if errors.recruitment_cost_per_employee}
                                    <p class="text-sm text-destructive">{errors.recruitment_cost_per_employee}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Training Settings Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Training Settings</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Training Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="training_cost_per_employee">
                                    Training Cost per Employee <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="training_cost_per_employee"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="kt-input {errors.training_cost_per_employee ? 'kt-input-error' : ''}"
                                    placeholder="0.00"
                                    bind:value={form.training_cost_per_employee}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Cost to train one employee for this profile
                                </p>
                                {#if errors.training_cost_per_employee}
                                    <p class="text-sm text-destructive">{errors.training_cost_per_employee}</p>
                                {/if}
                            </div>

                            <!-- Training Duration -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="training_duration_days">
                                    Training Duration (Days) <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="training_duration_days"
                                    type="number"
                                    min="1"
                                    class="kt-input {errors.training_duration_days ? 'kt-input-error' : ''}"
                                    placeholder="7"
                                    bind:value={form.training_duration_days}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Number of days required to fully train an employee
                                </p>
                                {#if errors.training_duration_days}
                                    <p class="text-sm text-destructive">{errors.training_duration_days}</p>
                                {/if}
                            </div>
                        </div>
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
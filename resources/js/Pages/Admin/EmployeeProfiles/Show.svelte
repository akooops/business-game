<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let employeeProfile;

    // Define breadcrumbs for this employee profile
    const breadcrumbs = [
        {
            title: 'Employee Profiles',
            url: route('admin.employee-profiles.index'),
            active: false
        },
        {
            title: employeeProfile.name || 'Employee Profile Details',
            url: route('admin.employee-profiles.show', { employeeProfile: employeeProfile.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Employee Profile Details';

    // Recruitment difficulty badge colors
    function getDifficultyBadgeClass(difficulty) {
        switch(difficulty) {
            case 'very_easy':
                return 'kt-badge kt-badge-outline kt-badge-success';
            case 'easy':
                return 'kt-badge kt-badge-outline kt-badge-success';
            case 'medium':
                return 'kt-badge kt-badge-outline kt-badge-warning';
            case 'hard':
                return 'kt-badge kt-badge-outline kt-badge-danger';
            case 'very_hard':
                return 'kt-badge kt-badge-outline kt-badge-danger';
            default:
                return 'kt-badge kt-badge-outline';
        }
    }

    // Format difficulty display name
    function getDifficultyDisplayName(difficulty) {
        switch(difficulty) {
            case 'very_easy':
                return 'Very Easy';
            case 'easy':
                return 'Easy';
            case 'medium':
                return 'Medium';
            case 'hard':
                return 'Hard';
            case 'very_hard':
                return 'Very Hard';
            default:
                return difficulty;
        }
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Employee Profile Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Employee Profile Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View employee profile information and settings
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.employee-profiles.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Employee Profiles
                    </a>
                    {#if hasPermission('admin.employee-profiles.update')}
                    <a href="{route('admin.employee-profiles.edit', { employeeProfile: employeeProfile.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Profile
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Profile Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Profile Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Profile Name</h4>
                            <p class="text-sm text-secondary-foreground">{employeeProfile?.name}</p>
                        </div>

                        <!-- Description -->
                        {#if employeeProfile?.description}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Description</h4>
                            <p class="text-sm text-secondary-foreground">{employeeProfile?.description}</p>
                        </div>
                        {/if}

                        <!-- Skills -->
                        {#if employeeProfile?.skills && employeeProfile.skills.length > 0}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Skills & Competencies</h4>
                            <div class="flex flex-wrap gap-2">
                                {#each employeeProfile.skills as skill}
                                    <span class="kt-badge kt-badge-outline kt-badge-sm">{skill}</span>
                                {/each}
                            </div>
                        </div>
                        {/if}

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(employeeProfile?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(employeeProfile?.updated_at)}
                            </p>
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
                    <div class="grid gap-4 w-full md:grid-cols-3">
                        <!-- Minimum Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Minimum Monthly Salary</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.monthly_min_salary}
                            </p>
                        </div>

                        <!-- Average Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Average Monthly Salary</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.monthly_avg_salary}
                            </p>
                        </div>

                        <!-- Maximum Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Monthly Salary</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.monthly_max_salary}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recruitment Settings Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Recruitment Settings</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full md:grid-cols-2">
                        <!-- Recruitment Difficulty -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Recruitment Difficulty</h4>
                            <div class="flex items-center gap-2">
                                <span class={getDifficultyBadgeClass(employeeProfile?.recruitment_difficulty)}>
                                    {getDifficultyDisplayName(employeeProfile?.recruitment_difficulty)}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                How difficult it is to find and recruit employees for this profile
                            </p>
                        </div>

                        <!-- Recruitment Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Recruitment Cost per Employee</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.recruitment_cost_per_employee}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Cost to recruit one employee (advertising, interviews, etc.)
                            </p>
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
                    <div class="grid gap-4 w-full md:grid-cols-2">
                        <!-- Training Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Training Cost per Employee</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.training_cost_per_employee}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Cost to train one employee for this profile
                            </p>
                        </div>

                        <!-- Training Duration -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Training Duration</h4>
                            <div class="flex items-center gap-2">
                                <p class="text-lg font-bold">
                                    {employeeProfile?.training_duration_days} days
                                </p>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Number of days required to fully train an employee
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 
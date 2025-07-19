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
                                ${employeeProfile?.min_salary_month}
                            </p>
                        </div>

                        <!-- Average Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Average Monthly Salary</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.avg_salary_month}
                            </p>
                        </div>

                        <!-- Maximum Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Monthly Salary</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.max_salary_month}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recruitment Cost Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Recruitment Cost</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full md:grid-cols-3">
                        <!-- Minimum Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Minimum Recruitment Cost</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.min_recruitment_cost}
                            </p>
                        </div>

                        <!-- Average Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Average Recruitment Cost</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.avg_recruitment_cost}
                            </p>
                        </div>

                        <!-- Maximum Salary -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Recruitment Cost</h4>
                            <p class="text-lg font-bold">
                                ${employeeProfile?.max_recruitment_cost}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 
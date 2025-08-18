<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let company;

    // Define breadcrumbs for this user
    const breadcrumbs = [
        {
            title: 'Companies',
            url: route('admin.companies.index'),
            active: false
        },
        {
            title: `${company.user.firstname} ${company.user.lastname}` || 'Company Details',
            url: route('admin.companies.show', { company: company.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Company Details';
</script>

<svelte:head>
    <title>Business game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- User Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Company Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View company information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.companies.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Companies
                    </a>
                    <a href="{route('admin.companies.edit', { company: company.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Company
                    </a>
                </div>
            </div>

            <!-- User Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- User Details -->
                     <div class="grid gap-4 w-full">
                        <!-- User Thumbnail -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={company.user.avatarUrl} 
                                    alt={company.user.fullname}
                                    class="rounded-lg w-32 h-32 object-cover"
                                />
                            </figure>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">User Name</h4>
                            <p class="text-sm text-secondary-foreground">@{company.user.username}</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">User Name</h4>
                            <p class="text-sm text-secondary-foreground">{company.user.fullname}</p>
                        </div>

                        <!-- User Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">User email</h4>
                            <p class="text-sm text-secondary-foreground">{company.user.email}</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(company.user.created_at)}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(company.user.updated_at)}
                            </p>
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
                    <!-- Company Details -->
                    <div class="grid gap-4 w-full">
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Funds</h4>
                            <p class="text-sm text-secondary-foreground">{company.funds} DZD</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Unpaid Loans</h4>
                            <p class="text-sm text-secondary-foreground">{company.unpaid_loans} DZD</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Carbon Footprint</h4>
                            <p class="text-sm text-secondary-foreground">{company.carbon_footprint} kg CO2</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Research Level</h4>
                            <p class="text-sm text-secondary-foreground">{company.research_level}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 
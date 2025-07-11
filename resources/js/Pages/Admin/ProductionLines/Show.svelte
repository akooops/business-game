<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';

    // Props passed from controller
    export let productionLine;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Production Lines',
            url: route('admin.production-lines.index'),
            active: false
        },
        {
            title: productionLine.name || 'Production Line Details',
            url: route('admin.production-lines.show', { productionLine: productionLine.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Production Line Details';

    // Map products for display
    const outputs = (productionLine.products || []).map(product => ({
        product_id: product.id,
        product_name: product.name,
        product_type_name: product.type_name,
        product_image: product.image_url
    }));

    // Map steps for display
    const steps = productionLine.steps || [];
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
                    <h1 class="text-2xl font-bold text-mono">Production Line Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View production line information, outputs and manufacturing steps
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.production-lines.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Production Lines
                    </a>
                    {#if hasPermission('admin.production-lines.update')}
                    <a href="{route('admin.production-lines.edit', { productionLine: productionLine.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Production Line
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
                    <div class="grid gap-4 w-full">
                        <!-- Production Line Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Production Line Name</h4>
                            <p class="text-sm text-secondary-foreground">{productionLine.name}</p>
                        </div>

                        <!-- Description -->
                        {#if productionLine.description}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Description</h4>
                            <p class="text-sm text-secondary-foreground">{productionLine.description}</p>
                        </div>
                        {/if}

                        <!-- Area Required -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Area Required</h4>
                            <p class="text-sm text-secondary-foreground">
                                {productionLine.area_required} sq m
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Floor space required)
                                </span>
                            </p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(productionLine.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(productionLine.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Outputs Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Production Outputs</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4">
                        {#if outputs.length === 0}
                            <div class="kt-card bg-muted/20 border-dashed">
                                <div class="kt-card-content text-center py-8">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-sm text-muted-foreground">No products defined yet</p>
                                    <p class="text-xs text-muted-foreground mt-1">This production line has no product outputs</p>
                                </div>
                            </div>
                        {:else}
                            <div>
                                <h5 class="text-sm font-medium text-mono mb-3">
                                    Products ({outputs.length})
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    {#each outputs as output}
                                        <div class="kt-card">
                                            <div class="kt-card-content flex flex-col p-3 gap-3">
                                                <!-- Product Image -->
                                                <div class="kt-card flex items-center justify-center relative bg-accent/50 w-full h-[120px] shadow-none rounded">
                                                    {#if output.product_image}
                                                        <img alt="" class="h-[100px] w-[100px] object-cover rounded" src="{output.product_image}"/>
                                                    {:else}
                                                        <i class="ki-filled ki-abstract-26 text-3xl text-muted-foreground"></i>
                                                    {/if}
                                                </div>
                                                
                                                <!-- Product Info -->
                                                <div class="flex flex-col gap-1">
                                                    <h6 class="text-sm font-medium text-mono leading-5 line-clamp-2" title="{output.product_name}">
                                                        {output.product_name}
                                                    </h6>
                                                    <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit">
                                                        {output.product_type_name}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Production Steps Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Production Steps</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4">
                        {#if steps.length === 0}
                            <div class="kt-card bg-muted/20 border-dashed">
                                <div class="kt-card-content text-center py-8">
                                    <i class="ki-filled ki-route text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-sm text-muted-foreground">No steps defined yet</p>
                                    <p class="text-xs text-secondary-foreground mt-1">
                                        This production line has no manufacturing steps
                                    </p>
                                </div>
                            </div>
                        {:else}
                            <div>
                                <h5 class="text-sm font-medium text-mono mb-3">
                                    Manufacturing Steps ({steps.length})
                                </h5>
                                <div class="space-y-3">
                                    {#each steps as step, index}
                                        <div class="kt-card border">
                                            <div class="kt-card-content p-4">
                                                <div class="flex items-start gap-4">
                                                    <!-- Step Number -->
                                                    <div class="flex flex-col gap-2">
                                                        <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                                            Step {index + 1}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Step Content -->
                                                    <div class="flex-1 space-y-3">
                                                        <div class="flex flex-col gap-2">
                                                            <h6 class="text-sm font-semibold text-mono">Step Name</h6>
                                                            <p class="text-sm text-secondary-foreground">{step.name}</p>
                                                        </div>
                                                        
                                                        {#if step.description}
                                                        <div class="flex flex-col gap-2">
                                                            <h6 class="text-sm font-semibold text-mono">Step Description</h6>
                                                            <p class="text-sm text-secondary-foreground">{step.description}</p>
                                                        </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 
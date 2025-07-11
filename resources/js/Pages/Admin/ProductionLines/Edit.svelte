<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

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
            title: productionLine.name,
            url: route('admin.production-lines.show', { productionLine: productionLine.id }),
            active: false
        },
        {
            title: 'Edit',
            url: route('admin.production-lines.edit', { productionLine: productionLine.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Production Line';

    // Form data - pre-populate with existing data
    let formData = {
        name: productionLine.name || '',
        description: productionLine.description || '',
        area_required: productionLine.area_required || '',
        outputs: (productionLine.products || []).map(product => {
            // Handle different possible data structures for the product relationship
            return {
                product_id: product.id,
                product_name: product.name,
                product_type_name: product.type_name,
                product_image: product.image_url
            };
        }),
        steps: (productionLine.steps || []).map(step => ({
            name: step.name || '',
            description: step.description || ''
        }))
    };
    
    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Add new output automatically when product is selected
    function handleProductSelect(event) {
        const productId = event.detail.value;
        const productData = event.detail.data;
        
        // Check if product is already added
        const exists = formData.outputs.find(output => output.product_id === productId);
        if (!exists) {
            formData.outputs = [...formData.outputs, { 
                product_id: productId,
                product_name: productData.name,
                product_type_name: productData.type_name,
                product_image: productData.image_url || null
            }];
        }
        
        // Clear the select2
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
    }

    // Remove output
    function removeOutput(index) {
        formData.outputs = formData.outputs.filter((_, i) => i !== index);
    }

    // Add new step
    function addStep() {
        formData.steps = [...formData.steps, { name: '', description: '' }];
    }

    // Remove step
    function removeStep(index) {
        formData.steps = formData.steps.filter((_, i) => i !== index);
    }

    // Move step up
    function moveStepUp(index) {
        if (index > 0) {
            const temp = formData.steps[index];
            formData.steps[index] = formData.steps[index - 1];
            formData.steps[index - 1] = temp;
            formData.steps = [...formData.steps]; // Trigger reactivity
        }
    }

    // Move step down
    function moveStepDown(index) {
        if (index < formData.steps.length - 1) {
            const temp = formData.steps[index];
            formData.steps[index] = formData.steps[index + 1];
            formData.steps[index + 1] = temp;
            formData.steps = [...formData.steps]; // Trigger reactivity
        }
    }

    // Handle form submission
    async function handleSubmit() {
        if (loading) return;

        loading = true;
        errors = {};

        try {
            formData._method = 'PATCH';

            router.post(route('admin.production-lines.update', { productionLine: productionLine.id }), formData, {
                onError: (err) => {
                    errors = err;
                    loading = false;
                },
                onSuccess: () => {
                    loading = false;
                }
            });
        } catch (error) {
            console.error('Error updating production line:', error);
            loading = false;
        }
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        if (window.KTToast) {
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: message,
                variant: type === 'success' ? 'success' : 'destructive',
                position: 'bottom-right',
            });
        }
    }

    // Select2 component reference
    let productSelectComponent;
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
                    <h1 class="text-2xl font-bold text-mono">Edit Production Line</h1>
                    <p class="text-sm text-secondary-foreground">
                        Modify the production line details, outputs and manufacturing steps
                    </p>
                </div>
                <div class="flex items-center gap-3">

                    <a href="{route('admin.production-lines.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-black-left text-base"></i>
                        Back to Production Lines
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
                                    Production Line Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="name"
                                    type="text" 
                                    bind:value={formData.name}
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter production line name"
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
                                    bind:value={formData.description}
                                    class="kt-textarea {errors.description ? 'kt-input-error' : ''}"
                                    placeholder="Enter production line description"
                                    rows="3"
                                ></textarea>
                                {#if errors.description}
                                    <p class="text-sm text-destructive">{errors.description}</p>
                                {/if}
                            </div>

                            <!-- Area Required -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="area_required">
                                    Area Required (sq m) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="area_required"
                                    type="number" 
                                    bind:value={formData.area_required}
                                    class="kt-input {errors.area_required ? 'kt-input-error' : ''}"
                                    placeholder="Enter area in square meters"
                                    min="0"
                                    step="0.1"
                                    required
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Floor space required for this production line
                                </p>
                                {#if errors.area_required}
                                    <p class="text-sm text-destructive">{errors.area_required}</p>
                                {/if}
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
                            <!-- Product Selector at the top -->
                            <div>
                                <label class="text-sm font-medium text-mono" for="product-selector">
                                    Add Product Output
                                </label>
                                <p class="text-xs text-secondary-foreground mb-2">
                                    Search and select products that this production line will manufacture
                                </p>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product-selector"
                                    placeholder="Search and select products to add..."
                                    on:select={handleProductSelect}
                                    ajax={{
                                        url: route('admin.products.index'),
                                        dataType: 'json',
                                        delay: 300,
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                perPage: 10
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.products.map(product => ({
                                                    id: product.id,
                                                    text: `${product.name}`,
                                                    name: product.name,
                                                    type: product.type,
                                                    type_name: product.type_name,
                                                    image_url: product.image_url
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">' +
                                            (data.image_url ? '<img src="' + data.image_url + '" alt="" class="size-10 object-cover rounded">' : '<i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>') +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit mt-1">' + data.type_name + '</span>' +
                                            '</div>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>
                            
                            <!-- Selected Products Grid -->
                            <div>
                                {#if formData.outputs.length === 0}
                                    <div class="kt-card bg-muted/20 border-dashed">
                                        <div class="kt-card-content text-center py-8">
                                            <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                            <p class="text-sm text-muted-foreground">No products selected yet</p>
                                            <p class="text-xs text-muted-foreground mt-1">Use the search above to add product outputs</p>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        {#each formData.outputs as output, index}
                                            <div class="kt-card relative">
                                                <div class="kt-card-content flex flex-col p-3 gap-3">
                                                    <!-- Remove button -->
                                                    <button 
                                                        type="button" 
                                                        class="absolute top-2 right-2 kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-destructive hover:bg-destructive hover:text-destructive-foreground z-10"
                                                        on:click={() => removeOutput(index)}
                                                        title="Remove product"
                                                    >
                                                        <i class="ki-filled ki-cross text-xs"></i>
                                                    </button>
                                                    
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
                                {/if}

                                {#if errors.outputs}
                                    <p class="text-sm text-destructive mt-2">{errors.outputs}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Production Steps Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <div class="flex items-center justify-between w-full">
                            <h4 class="kt-card-title">Production Steps</h4>
                            <button type="button" class="kt-btn kt-btn-outline kt-btn-sm" on:click={addStep}>
                                <i class="ki-filled ki-plus text-sm"></i>
                                Add Step
                            </button>
                        </div>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            {#if formData.steps.length === 0}
                                <div class="kt-card bg-muted/20 border-dashed">
                                    <div class="kt-card-content text-center py-8">
                                        <i class="ki-filled ki-route text-2xl text-muted-foreground mb-2"></i>
                                        <p class="text-sm text-muted-foreground">No steps defined yet</p>
                                        <p class="text-xs text-secondary-foreground mt-1">
                                            Define the manufacturing steps for this production line
                                        </p>
                                        <button type="button" class="kt-btn kt-btn-primary kt-btn-sm mt-3" on:click={addStep}>
                                            Add First Step
                                        </button>
                                    </div>
                                </div>
                            {:else}
                                <div class="space-y-3">
                                    {#each formData.steps as step, index}
                                        <div class="kt-card border">
                                            <div class="kt-card-content p-4">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex flex-col gap-2">
                                                        <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                                            Step {index + 1}
                                                        </span>
                                                        <div class="flex flex-col gap-1">
                                                            <button 
                                                                type="button" 
                                                                class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost"
                                                                on:click={() => moveStepUp(index)}
                                                                disabled={index === 0}
                                                                title="Move up"
                                                            >
                                                                <i class="ki-filled ki-up"></i>
                                                            </button>
                                                            <button 
                                                                type="button" 
                                                                class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost"
                                                                on:click={() => moveStepDown(index)}
                                                                disabled={index === formData.steps.length - 1}
                                                                title="Move down"
                                                            >
                                                                <i class="ki-filled ki-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 space-y-3">
                                                        <div class="flex flex-col gap-2">
                                                            <label class="text-sm font-medium text-mono" for="step-name-{index}">
                                                                Step Name <span class="text-destructive">*</span>
                                                            </label>
                                                            <input 
                                                                id="step-name-{index}"
                                                                type="text" 
                                                                bind:value={step.name}
                                                                class="kt-input {errors[`steps.${index}.name`] ? 'kt-input-error' : ''}"
                                                                placeholder="Enter step name"
                                                                required
                                                            />
                                                            {#if errors[`steps.${index}.name`]}
                                                                <p class="text-sm text-destructive">{errors[`steps.${index}.name`]}</p>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col gap-2">
                                                            <label class="text-sm font-medium text-mono" for="step-description-{index}">
                                                                Step Description
                                                            </label>
                                                            <textarea 
                                                                id="step-description-{index}"
                                                                bind:value={step.description}
                                                                class="kt-textarea {errors[`steps.${index}.description`] ? 'kt-input-error' : ''}"
                                                                placeholder="Enter step description"
                                                                rows="2"
                                                            ></textarea>
                                                            {#if errors[`steps.${index}.description`]}
                                                                <p class="text-sm text-destructive">{errors[`steps.${index}.description`]}</p>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                        on:click={() => removeStep(index)}
                                                        title="Remove step"
                                                    >
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.production-lines.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Production Line
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs
    $: breadcrumbs = [
        {
            title: 'Products',
            url: route('admin.products.index'),
            active: false
        },
        {
            title: 'Recipe Management',
            url: route('admin.product-recipes.index'),
            active: !currentProduct
        },
        ...(currentProduct ? [{
            title: currentProduct.name,
            url: route('admin.product-recipes.index', { product_id: currentProduct.id }),
            active: true
        }] : [])
    ];
    
    const pageTitle = 'Product Recipe Management';

    // Reactive variables
    let productRecipes = [];
    let currentProduct = null;
    let recipeData = productRecipes || [];
    let loading = false;

    // Form state for recipe drawer
    let formData = {
        material_id: '',
        quantity: ''
    };

    // Form errors
    let errors = {};

    // Edit mode state
    let isEditMode = false;
    let editingRecipe = null;

    // Product selector
    let selectedProductId = currentProduct?.id || '';
    let productSelectComponent;
    let materialSelectComponent;

    // Drawer management functions
    function openDrawer() {
        // Reset to create mode
        isEditMode = false;
        editingRecipe = null;
        errors = {};
        
        formData = {
            material_id: '',
            quantity: ''
        };
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#recipe_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    function openEditDrawer(recipe) {
        // Set edit mode
        isEditMode = true;
        editingRecipe = recipe;
        errors = {};
        
        formData = {
            material_id: recipe.material_id,
            quantity: recipe.quantity
        };
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#recipe_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    async function handleSubmit() {
        if (!currentProduct) {
            showToast('Please select a product first', 'error');
            return;
        }

        try {
            // Clear previous errors
            errors = {};

            const url = isEditMode 
                ? route('admin.product-recipes.update', editingRecipe.id)
                : route('admin.product-recipes.store');
            
            const method = 'POST';

            // Prepare request body
            let requestBody;
            let headers;
            
            if (isEditMode) {
                // For PATCH requests, use FormData with _method
                const patchFormData = new FormData();
                patchFormData.append('_method', 'PATCH');
                patchFormData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
                Object.keys(formData).forEach(key => {
                    if (formData[key] !== '') {
                        patchFormData.append(key, formData[key]);
                    }
                });
                requestBody = patchFormData;
                headers = {
                    'X-Requested-With': 'XMLHttpRequest'
                };
            } else {
                // For POST requests, use JSON
                requestBody = JSON.stringify({
                    product_id: currentProduct.id,
                    ...formData
                });
                headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                };
            }

            const response = await fetch(url, {
                method: method,
                headers: headers,
                body: requestBody
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                const message = data.message || (isEditMode ? "Recipe updated successfully!" : "Material added successfully!");
                showToast(message, 'success');

                // Close drawer
                const dismissButton = document.querySelector('#cancel-button');
                if (dismissButton) {
                    dismissButton.click();
                }
                
                // Reset form data and state
                formData = {
                    material_id: '',
                    quantity: '',
                };
                
                isEditMode = false;
                editingRecipe = null;
                errors = {};
                
                // Refresh recipe data
                await fetchRecipeData();
            } else {
                // Handle error response
                const errorData = await response.json();
                errors = errorData.errors || {};
                
                // Show error toast
                const errorMessage = errorData.message || `Error ${isEditMode ? 'updating' : 'adding'} material. Please try again.`;
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error(`Error ${isEditMode ? 'updating' : 'adding'} material:`, error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    async function deleteRecipe(recipe) {
        if (!confirm('Are you sure you want to remove this material from the recipe? This action cannot be undone.')) {
            return;
        }

        try {
            const deleteFormData = new FormData();
            deleteFormData.append('_method', 'DELETE');
            deleteFormData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.product-recipes.destroy', recipe.id), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: deleteFormData
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                const message = data.message || 'Material removed successfully!';
                showToast(message, 'success');

                // Close drawer if open
                const dismissButton = document.querySelector('#cancel-button');
                if (dismissButton) {
                    dismissButton.click();
                }

                // Refresh recipe data
                await fetchRecipeData();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error removing material. Please try again.';
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error removing material:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    // Handle product selection
    function handleProductSelect(event) {
        selectedProductId = event.detail.value;
        if (event.detail.data) {
            currentProduct = {
                id: event.detail.data.id,
                name: event.detail.data.name,
            };
            
            // Update URL with product information
            const url = new URL(window.location.href);
            url.searchParams.set('product_id', currentProduct.id);
            url.searchParams.set('product_name', currentProduct.name);
            window.history.pushState({}, '', url.toString());
        }
        fetchRecipeData();
    }

    // Handle product clear
    function handleProductClear() {
        selectedProductId = '';
        currentProduct = null;
        recipeData = [];
        
        // Update URL to remove product_id parameter
        const url = new URL(window.location.href);
        url.searchParams.delete('product_id');
        window.history.pushState({}, '', url.toString());
    }

    // Handle material selection for navigation
    function handleMaterialClick(material) {
        currentProduct = {
            id: material.id,
            name: material.name,
        };
        selectedProductId = material.id;
        
        // Update URL
        const url = new URL(window.location.href);
        url.searchParams.set('product_id', currentProduct.id);
        url.searchParams.set('product_name', currentProduct.name);
        window.history.pushState({}, '', url.toString());
        
        fetchRecipeData();
    }

    // Fetch recipe data using index endpoint
    async function fetchRecipeData() {
        if (!selectedProductId) {
            recipeData = [];
            return;
        }

        loading = true;
        try {
            const response = await fetch(route('admin.product-recipes.index', { 
                product_id: selectedProductId
            }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            if (data.productRecipes) {
                recipeData = data.productRecipes;
            }
        } catch (error) {
            console.error('Error fetching recipe data:', error);
            showToast('Error loading recipe data', 'error');
        } finally {
            loading = false;
        }
    }

    // Handle material selection
    function handleMaterialSelect(event) {
        formData.material_id = event.detail.value;
    }

    // Handle material clear
    function handleMaterialClear() {
        formData.material_id = '';
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

    // Initialize on mount
    onMount(async () => {
        // Check if product_id is passed in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const initialProductId = urlParams.get('product_id');
        const initialProductName = urlParams.get('product_name');
        
        if (initialProductId) {
            selectedProductId = initialProductId;
            currentProduct = {
                id: initialProductId,
                name: initialProductName || `Product #${initialProductId}`
            };
            fetchRecipeData();
        }
    });
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
                    <h1 class="text-2xl font-bold text-mono">Product Recipe Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage materials and quantities required for production
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {#if currentProduct}
                        <button 
                            class="kt-btn kt-btn-primary"
                            disabled={loading}
                            title="Add material to recipe"
                            on:click={openDrawer}
                        >
                            <i class="ki-filled ki-plus text-base"></i>
                            Add Material
                        </button>
                        <button style="display:none" data-kt-drawer-toggle="#recipe_drawer"></button>
                    {/if}
                </div>
            </div>

            <!-- Product Selector -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-mono mb-2 block">Select Product</label>
                            <div class="flex items-center gap-3">
                                {#if currentProduct}
                                    <!-- Product Badge -->
                                    <div class="flex items-center gap-2">
                                        <span class="kt-badge kt-badge-outline kt-badge-primary">
                                            <i class="ki-filled ki-abstract-26 text-sm me-1"></i>
                                            Product: {currentProduct.name}
                                        </span>
                                        <button 
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost"
                                            on:click={handleProductClear}
                                            title="Clear product selection"
                                        >
                                            <i class="ki-filled ki-cross text-sm"></i>
                                        </button>
                                    </div>
                                {:else}
                                    <!-- Product Filter -->
                                    <div class="w-full">
                                        <Select2
                                            bind:this={productSelectComponent}
                                            id="product-select"
                                            placeholder="Choose a product..."
                                            bind:value={selectedProductId}
                                            on:select={handleProductSelect}
                                            on:clear={handleProductClear}
                                            disabled={loading}
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
                                                            type_name: product.type_name
                                                        }))
                                                    };
                                                },
                                                cache: true
                                            }}
                                            templateResult={function(data) {
                                                if (data.loading) return data.text;
                                                
                                                const $elem = globalThis.$('<div class="flex flex-col">' +
                                                    '<span class="font-medium">' + data.name + '</span>' +
                                                    '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit mt-1">' + data.type_name + '</span>' +
                                                    '</div>');
                                                return $elem;
                                            }}
                                            templateSelection={function(data) {
                                                if (!data.id) return data.text;
                                                
                                                return data.name + ' (' + data.type_name + ')';
                                            }}
                                        />
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {#if currentProduct}
                <!-- Recipe Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Recipe Materials</h4>
                    </div>
                    <div class="kt-card-content p-0">
                        {#if loading}
                            <div class="flex justify-center items-center h-48">
                                <div class="kt-spinner kt-spinner-primary"></div>
                            </div>
                        {:else if recipeData.length === 0}
                            <div class="flex flex-col items-center justify-center h-48 text-center p-8">
                                <i class="ki-filled ki-element-11 text-4xl text-muted-foreground mb-4"></i>
                                <h3 class="text-lg font-semibold text-mono mb-2">No materials in recipe</h3>
                                <p class="text-sm text-secondary-foreground">
                                    Add materials to define the recipe for this product
                                </p>
                            </div>
                        {:else}
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#if loading}
                                            <!-- Loading skeleton rows -->
                                            {#each Array(5) as _, i}
                                                <tr>
                                                    <td class="p-4">
                                                        <div class="kt-skeleton w-32 h-4 rounded"></div>
                                                    </td>
                                                    <td class="p-4">
                                                        <div class="kt-skeleton w-20 h-6 rounded"></div>
                                                    </td>
                                                    <td class="p-4">
                                                        <div class="kt-skeleton w-16 h-4 rounded"></div>
                                                    </td>
                                                    <td class="p-4">
                                                        <div class="flex items-center gap-2">
                                                            <div class="kt-skeleton w-8 h-8 rounded"></div>
                                                            <div class="kt-skeleton w-8 h-8 rounded"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/each}
                                        {:else}
                                            {#each recipeData as recipe}
                                                <tr>
                                                    <td>
                                                        <button 
                                                            class="text-left font-medium text-primary hover:text-primary-active cursor-pointer"
                                                            on:click={() => handleMaterialClick(recipe.material)}
                                                            title="View recipe for this material"
                                                        >
                                                            {recipe.material.name}
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                                            {recipe.material.type_name || recipe.material.type}
                                                        </span>
                                                    </td>
                                                    <td class="font-medium">{recipe.quantity}</td>
                                                    <td>
                                                        <div class="flex items-center gap-2">
                                                            <button 
                                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost"
                                                                title="Edit"
                                                                on:click={() => openEditDrawer(recipe)}
                                                            >
                                                                <i class="ki-filled ki-pencil"></i>
                                                            </button>
                                                            <button 
                                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                                title="Remove"
                                                                on:click={() => deleteRecipe(recipe)}
                                                            >
                                                                <i class="ki-filled ki-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/each}
                                        {/if}
                                    </tbody>
                                </table>
                            </div>
                        {/if}
                    </div>
                </div>
            {:else}
                <!-- No Product Selected -->
                <div class="kt-card">
                    <div class="kt-card-content">
                        <div class="flex flex-col items-center justify-center h-96 text-center">
                            <i class="ki-filled ki-element-11 text-4xl text-muted-foreground mb-4"></i>
                            <h3 class="text-lg font-semibold text-mono mb-2">Select a Product</h3>
                            <p class="text-sm text-secondary-foreground">
                                Choose a product from the dropdown above to view and manage its recipe materials and quantities.
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <!-- Recipe Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="recipe_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            {isEditMode ? 'Edit Recipe Material' : 'Add Recipe Material'}
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="p-5">
            <form on:submit|preventDefault={handleSubmit} class="space-y-4">
                {#if !isEditMode}
                    <div>
                        <label class="block text-sm font-medium text-mono mb-2">Material</label>
                        <Select2
                            bind:this={materialSelectComponent}
                            id="material-select"
                            placeholder="Choose a material..."
                            bind:value={formData.material_id}
                            on:select={handleMaterialSelect}
                            on:clear={handleMaterialClear}
                            disabled={loading}
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
                                        results: data.products.filter(product => product.id != currentProduct?.id).map(product => ({
                                            id: product.id,
                                            text: `${product.name}`,
                                            name: product.name,
                                            type: product.type,
                                            type_name: product.type_name
                                        }))
                                    };
                                },
                                cache: true
                            }}
                            templateResult={function(data) {
                                if (data.loading) return data.text;
                                
                                const $elem = globalThis.$('<div class="flex flex-col">' +
                                    '<span class="font-medium">' + data.name + '</span>' +
                                    '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit mt-1">' + data.type_name + '</span>' +
                                    '</div>');
                                return $elem;
                            }}
                            templateSelection={function(data) {
                                if (!data.id) return data.text;
                                
                                return data.name + ' (' + data.type_name + ')';
                            }}
                        />
                        {#if errors.material_id}
                            <p class="text-sm text-destructive mt-1">{errors.material_id}</p>
                        {/if}
                    </div>
                {/if}

                <div>
                    <label class="block text-sm font-medium text-mono mb-2">Quantity</label>
                    <input 
                        type="number" 
                        bind:value={formData.quantity}
                        min="0.0001 "
                        step="any"
                        class="kt-input w-full {errors.quantity ? 'kt-input-error' : ''}"
                        required
                    />
                    {#if errors.quantity}
                        <p class="text-sm text-destructive mt-1">{errors.quantity}</p>
                    {/if}
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button id="cancel-button" type="button" class="kt-btn kt-btn-outline flex-1" data-kt-drawer-dismiss="true">
                        Cancel
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary flex-1">
                        {isEditMode ? 'Update Material' : 'Add Material'}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 
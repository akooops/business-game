<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs - make reactive to include product name
    $: breadcrumbs = [
        {
            title: 'Products',
            url: route('admin.products.index'),
            active: false
        },
        {
            title: 'Demand Management',
            url: route('admin.product-demand.index'),
            active: !currentProduct
        },
        ...(currentProduct ? [{
            title: currentProduct.name,
            url: route('admin.product-demand.index', { product_id: currentProduct.id }),
            active: true
        }] : [])
    ];
    
    const pageTitle = 'Product Demand Management';

    // Reactive variables
    let productDemands = [];
    let currentProduct = null;
    let demandData = productDemands || [];
    let loading = false;
    let showTable = true; // Toggle between chart and table view
    let chart = null;

    // Form state for gameweek drawer
    let formData = {
        gameweek: '',
        min_demand: '',
        avg_demand: '',
        max_demand: '',
        market_price: '',
        visibility_cost: '',
        research_time_days: '',
        is_visible: true
    };

    // Form errors
    let errors = {};

    // Edit mode state
    let isEditMode = false;
    let editingGameweek = null;

    // Product selector
    let selectedProductId = currentProduct?.id || '';
    let productSelectComponent;

    // Drawer management functions
    function openDrawer() {
        // Reset to create mode
        isEditMode = false;
        editingGameweek = null;
        errors = {};
        
        // Get next gameweek number
        const nextGameweek = demandData.length > 0 ? Math.max(...demandData.map(d => parseInt(d.gameweek))) + 1 : 1;
        
        formData = {
            gameweek: nextGameweek,
            min_demand: '',
            avg_demand: '',
            max_demand: '',
            market_price: '',
            visibility_cost: '',
            research_time_days: '',
            is_visible: true
        };
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#gameweek_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    function openEditDrawer(gameweek) {
        // Set edit mode
        isEditMode = true;
        editingGameweek = gameweek;
        errors = {};
        
        formData = {
            gameweek: gameweek.gameweek,
            min_demand: gameweek.min_demand,
            avg_demand: gameweek.avg_demand,
            max_demand: gameweek.max_demand,
            market_price: gameweek.market_price,
            visibility_cost: gameweek.visibility_cost || '',
            research_time_days: gameweek.research_time_days || '',
            is_visible: gameweek.is_visible
        };
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#gameweek_drawer"]');
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
                ? route('admin.product-demand.update', editingGameweek.id)
                : route('admin.product-demand.store');
            
            const method = 'POST';

            // Prepare request body
            let requestBody;
            let headers;
            
            // Prepare cleaned form data with proper boolean conversion
            const cleanedFormData = {
                ...formData,
                is_visible: formData.is_visible ? '1' : '0'
            };

            if (isEditMode) {
                // For PATCH requests, use FormData with _method
                const patchFormData = new FormData();
                patchFormData.append('_method', 'PATCH');
                patchFormData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
                patchFormData.append('product_id', currentProduct.id);
                Object.keys(cleanedFormData).forEach(key => {
                    patchFormData.append(key, cleanedFormData[key]);
                });
                requestBody = patchFormData;
                headers = {
                    'X-Requested-With': 'XMLHttpRequest'
                };
            } else {
                // For POST requests, use JSON
                requestBody = JSON.stringify({
                    product_id: currentProduct.id,
                    ...cleanedFormData
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
                const message = data.message || (isEditMode ? "Gameweek updated successfully!" : "Gameweek created successfully!");
                showToast(message, 'success');

                // Close drawer
                const dismissButton = document.querySelector('#cancel-button');
                if (dismissButton) {
                    dismissButton.click();
                }
                
                // Reset form data and state
                formData = {
                    gameweek: '',
                    min_demand: '',
                    avg_demand: '',
                    max_demand: '',
                    market_price: '',
                    visibility_cost: '',
                    research_time_days: '',
                    is_visible: true
                };
                
                isEditMode = false;
                editingGameweek = null;
                errors = {};
                
                // Refresh demand data
                await fetchDemandData();
                
                // Force chart update after a short delay to ensure data is loaded
                setTimeout(() => {
                    if (chart && !showTable && demandData.length > 0) {
                        updateChart();
                    }
                }, 100);
            } else {
                // Handle error response
                const errorData = await response.json();
                errors = errorData.errors || {};
                
                // Show error toast
                const errorMessage = errorData.message || `Error ${isEditMode ? 'updating' : 'creating'} gameweek. Please try again.`;
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error(`Error ${isEditMode ? 'updating' : 'creating'} gameweek:`, error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    async function deleteGameweek(gameweek) {
        if (!confirm('Are you sure you want to delete this gameweek? This action cannot be undone.')) {
            return;
        }

        try {
            const deleteFormData = new FormData();
            deleteFormData.append('_method', 'DELETE');
            deleteFormData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.product-demand.destroy', gameweek.id), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: deleteFormData
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                const message = data.message || 'Gameweek deleted successfully!';
                showToast(message, 'success');

                // Close drawer if open
                const dismissButton = document.querySelector('#cancel-button');
                if (dismissButton) {
                    dismissButton.click();
                }

                // Refresh demand data
                await fetchDemandData();
                
                // Force chart update after a short delay to ensure data is loaded
                setTimeout(() => {
                    if (chart && !showTable) {
                        updateChart();
                    }
                }, 100);
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting gameweek. Please try again.';
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error deleting gameweek:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    // ApexCharts configuration
    let chartOptions = {
        chart: {
            id: 'demand-chart',
            type: 'line',
            height: 450,
            toolbar: {
                show: false
            }
        },
        stroke: {
            width: [3, 3, 3],
            curve: 'smooth'
        },
        xaxis: {
            title: {
                text: 'Gameweek'
            },
            type: 'numeric',
            labels: {
                formatter: function(val) {
                    return Math.floor(val).toString();
                }
            },
            tickAmount: 10
        },
        yaxis: {
            title: {
                text: 'Demand (Units)'
            },
            min: 0
        },
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'center',
            verticalAlign: 'bottom',
            itemMargin: {
                horizontal: 15,
                vertical: 0
            },
            markers: {
                width: 12,
                height: 12
            },
            fontSize: '12px',
        },
        colors: ['#EF4444', '#10B981', '#3B82F6'],
        series: []
    };

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
        fetchDemandData();
    }

    // Handle product clear
    function handleProductClear() {
        selectedProductId = '';
        currentProduct = null;
        demandData = [];
        updateChart();
        
        // Update URL to remove product_id parameter
        const url = new URL(window.location.href);
        url.searchParams.delete('product_id');
        window.history.pushState({}, '', url.toString());
    }

    // Fetch demand data using index endpoint
    async function fetchDemandData() {
        if (!selectedProductId) {
            demandData = [];
            if (chart) {
                chart.updateSeries([]);
            }
            return;
        }

        loading = true;
        try {
            const response = await fetch(route('admin.product-demand.index', { 
                product_id: selectedProductId
            }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            if (data.productDemands) {
                demandData = data.productDemands;
                
                showTable = true;

                // Force chart update after data change
                if (chart && !showTable) {
                    updateChart();
                }
            }
        } catch (error) {
            console.error('Error fetching demand data:', error);
            showToast('Error loading demand data', 'error');
        } finally {
            loading = false;
        }
    }

    // Update ApexCharts with demand data
    function updateChart() {
        if (!chart) {
            console.log('Chart not initialized yet');
            return;
        }

        if (!demandData || !demandData.length) {
            console.log('No data to display in chart');
            chart.updateSeries([]);
            return;
        }

        const series = [
            {
                name: 'Min Demand',
                type: 'line',
                data: demandData.map(d => ({ x: parseInt(d.gameweek), y: parseFloat(d.min_demand) }))
            },
            {
                name: 'Average Demand',
                type: 'line',
                data: demandData.map(d => ({ x: parseInt(d.gameweek), y: parseFloat(d.avg_demand) }))
            },
            {
                name: 'Max Demand',
                type: 'line',
                data: demandData.map(d => ({ x: parseInt(d.gameweek), y: parseFloat(d.max_demand) }))
            }
        ];

        console.log('Updating chart with series:', series);
        
        try {
            chart.updateSeries(series, true); // Force redraw
        } catch (error) {
            console.error('Error updating chart:', error);
            // Try to reinitialize chart if update fails
            chart.destroy();
            chart = null;
            setTimeout(() => {
                initChart();
            }, 100);
        }
    }

    // Initialize ApexChart
    async function initChart() {
        await tick();
        const chartElement = document.querySelector('#demand-chart');
        
        console.log('Chart element:', chartElement);
        console.log('ApexCharts available:', !!window.ApexCharts);
        
        if (chartElement && window.ApexCharts) {
            try {
                chart = new ApexCharts(chartElement, chartOptions);
                await chart.render();
                console.log('Chart initialized successfully');
                updateChart();
            } catch (error) {
                console.error('Error initializing chart:', error);
            }
        } else {
            console.error('Chart element or ApexCharts not found');
            if (!chartElement) console.error('Chart element #demand-chart not found');
            if (!window.ApexCharts) console.error('ApexCharts not loaded');
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
            fetchDemandData();
        }
        
        if (currentProduct) {
            // Set initial value for Select2 if product is already selected
            selectedProductId = currentProduct.id;
            if (productSelectComponent) {
                // Add the selected product to Select2's options
                const option = new Option(`${currentProduct.name} (${currentProduct.type})`, currentProduct.id, true, true);
                productSelectComponent.setValue(currentProduct.id);
            }
        }
    });

    // Initialize chart when switching to chart view
    $: if (currentProduct && !showTable && !chart && !loading) {
        // Wait for DOM to update then initialize chart
        tick().then(() => {
            setTimeout(() => {
                initChart();
            }, 100);
        });
    }

    // Clean up chart when switching to table view
    $: if (showTable && chart) {
        chart.destroy();
        chart = null;
    }

    // Reactive updates for chart
    $: if (chart && demandData) {
        updateChart();
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>

    <style>
        .apexcharts-legend.apexcharts-align-center.apx-legend-position-top{
            flex-direction: row;
        }
    </style>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Product Demand Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage market demand data and pricing for simulation products
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {#if currentProduct}
                        <button 
                            class="kt-btn kt-btn-primary"
                            disabled={loading}
                            title="Add new gameweek data"
                            on:click={openDrawer}
                        >
                            <i class="ki-filled ki-plus text-base"></i>
                            Add Gameweek
                        </button>
                        <button style="display:none" data-kt-drawer-toggle="#gameweek_drawer"></button>
                    {/if}
                </div>
            </div>

            <!-- Product Selector -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex flex-col">
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
                <!-- Data View Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Demand & Price Trends</h4>
                        <div class="flex items-center gap-2">
                            <button 
                                class="kt-btn {showTable ? 'kt-btn-primary' : 'kt-btn-outline'}"
                                on:click={() => showTable = true}
                            >
                                <i class="ki-filled ki-row-horizontal text-base"></i>
                                Table
                            </button>
                            <button 
                                class="kt-btn {!showTable ? 'kt-btn-primary' : 'kt-btn-outline'}"
                                on:click={() => showTable = false}
                            >
                                <i class="ki-filled ki-chart-line text-base"></i>
                                Chart
                            </button>
                        </div>
                    </div>
                    <div class="kt-card-content {showTable ? 'p-0' : ''}">
                        {#if !showTable}
                            <!-- Chart View -->
                            {#if loading}
                                <div class="flex justify-center items-center h-96">
                                    <div class="kt-spinner kt-spinner-primary"></div>
                                </div>
                            {:else if demandData.length === 0}
                                <div class="flex flex-col items-center justify-center h-96 text-center p-8">
                                    <i class="ki-filled ki-chart-line text-4xl text-muted-foreground mb-4"></i>
                                    <h3 class="text-lg font-semibold text-mono mb-2">No demand data available</h3>
                                    <p class="text-sm text-secondary-foreground">
                                        Add gameweek data to see the chart
                                    </p>
                                </div>
                            {:else}
                                <div id="demand-chart" style="min-height: 400px;"></div>
                            {/if}
                        {:else}
                            <!-- Table View -->
                            {#if loading}
                                <div class="flex justify-center items-center h-48">
                                    <div class="kt-spinner kt-spinner-primary"></div>
                                </div>
                            {:else if demandData.length === 0}
                                <div class="flex flex-col items-center justify-center h-48 text-center p-8">
                                    <i class="ki-filled ki-row-horizontal text-4xl text-muted-foreground mb-4"></i>
                                    <h3 class="text-lg font-semibold text-mono mb-2">No demand data available</h3>
                                    <p class="text-sm text-secondary-foreground">
                                        Add gameweek data to see the table
                                    </p>
                                </div>
                            {:else}
                                <div class="kt-scrollable-x-auto">
                                    <table class="kt-table kt-table-border">
                                        <thead>
                                            <tr>
                                                <th>Gameweek</th>
                                                <th>Min Demand</th>
                                                <th>Avg Demand</th>
                                                <th>Max Demand</th>
                                                <th>Market Price</th>
                                                <th>Visibility Cost</th>
                                                <th>Research Time</th>
                                                <th>Visible</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {#each demandData.sort((a, b) => b.gameweek - a.gameweek) as demand}
                                                <tr class="{!demand.is_visible ? 'opacity-60' : ''}">
                                                    <td>
                                                        <span class="font-medium text-mono">Week {demand.gameweek}</span>
                                                    </td>
                                                    <td>{demand.min_demand}</td>
                                                    <td class="font-medium">{demand.avg_demand}</td>
                                                    <td>{demand.max_demand}</td>
                                                    <td>DZD{demand.market_price}</td>
                                                    <td>{demand.visibility_cost ? 'DZD' + demand.visibility_cost : '-'}</td>
                                                    <td>{demand.research_time_days ? demand.research_time_days + ' days' : '-'}</td>
                                                    <td>
                                                        {#if demand.is_visible}
                                                            <span class="kt-badge kt-badge-success kt-badge-sm">Visible</span>
                                                        {:else}
                                                            <span class="kt-badge kt-badge-outline kt-badge-sm">Hidden</span>
                                                        {/if}
                                                    </td>
                                                    <td>
                                                        <div class="flex items-center gap-2">
                                                            <button 
                                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost"
                                                                title="Edit"
                                                                on:click={() => openEditDrawer(demand)}
                                                            >
                                                                <i class="ki-filled ki-pencil"></i>
                                                            </button>
                                                            <button 
                                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                                title="Delete"
                                                                on:click={() => deleteGameweek(demand)}
                                                            >
                                                                <i class="ki-filled ki-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/each}
                                        </tbody>
                                    </table>
                                </div>
                            {/if}
                        {/if}
                    </div>
                </div>
            {:else}
                <!-- No Product Selected -->
                <div class="kt-card">
                    <div class="kt-card-content">
                        <div class="flex flex-col items-center justify-center h-96 text-center">
                            <i class="ki-filled ki-chart-line text-4xl text-muted-foreground mb-4"></i>
                            <h3 class="text-lg font-semibold text-mono mb-2">Select a Product</h3>
                            <p class="text-sm text-secondary-foreground">
                                Choose a product from the dropdown above to view and manage its demand data, pricing, and market visibility settings.
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <!-- Gameweek Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="gameweek_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            {isEditMode ? 'Edit Gameweek' : 'Add Gameweek'}
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="grow flex flex-col kt-scrollable-y-auto">
            <div class="p-5">
                <form on:submit|preventDefault={handleSubmit} class="space-y-4">
                                    <div>
                    <label class="block text-sm font-medium text-mono mb-2">Gameweek</label>
                    <input 
                        type="number" 
                        bind:value={formData.gameweek}
                        min="1"
                        class="kt-input w-full {errors.gameweek ? 'kt-input-error' : ''}"
                        disabled
                        required
                    />
                    {#if errors.gameweek}
                        <p class="text-sm text-destructive mt-1">{errors.gameweek}</p>
                    {/if}
                </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-mono mb-2">Min Demand</label>
                            <input 
                                type="number" 
                                bind:value={formData.min_demand}
                                min="0"
                                step="0.001"
                                class="kt-input w-full {errors.min_demand ? 'kt-input-error' : ''}"
                                required
                            />
                            {#if errors.min_demand}
                                <p class="text-sm text-destructive mt-1">{errors.min_demand}</p>
                            {/if}
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-mono mb-2">Avg Demand</label>
                            <input 
                                type="number" 
                                bind:value={formData.avg_demand}
                                min="0"
                                step="0.001"
                                class="kt-input w-full {errors.avg_demand ? 'kt-input-error' : ''}"
                                required
                            />
                            {#if errors.avg_demand}
                                <p class="text-sm text-destructive mt-1">{errors.avg_demand}</p>
                            {/if}
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-mono mb-2">Max Demand</label>
                            <input 
                                type="number" 
                                bind:value={formData.max_demand}
                                min="0"
                                step="0.001"
                                class="kt-input w-full {errors.max_demand ? 'kt-input-error' : ''}"
                                required
                            />
                            {#if errors.max_demand}
                                <p class="text-sm text-destructive mt-1">{errors.max_demand}</p>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-mono mb-2">Market Price (DZD)</label>
                        <input 
                            type="number" 
                            bind:value={formData.market_price}
                            min="0"
                            step="0.001"
                            class="kt-input w-full {errors.market_price ? 'kt-input-error' : ''}"
                            required
                        />
                        {#if errors.market_price}
                            <p class="text-sm text-destructive mt-1">{errors.market_price}</p>
                        {/if}
                    </div>

                    <!-- Research & Visibility Settings -->
                    <div class="border border-border rounded-lg p-4 bg-secondary/20">
                        <h4 class="text-sm font-semibold text-mono mb-3">Research & Visibility Settings</h4>
                        <p class="text-xs text-secondary-foreground mb-4">Settings for when data is not visible - costs a research time needed</p>
                        
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-mono mb-2">Research Time (Days)</label>
                            <input 
                                type="number" 
                                bind:value={formData.research_time_days}
                                min="0"
                                class="kt-input w-full {errors.research_time_days ? 'kt-input-error' : ''}"
                            />
                            {#if errors.research_time_days}
                                <p class="text-sm text-destructive mt-1">{errors.research_time_days}</p>
                            {/if}
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-mono mb-2">Visibility Cost (DZD)</label>
                            <input 
                                type="number" 
                                bind:value={formData.visibility_cost}
                                min="0"
                                step="0.001"
                                class="kt-input w-full {errors.visibility_cost ? 'kt-input-error' : ''}"
                            />
                            {#if errors.visibility_cost}
                                <p class="text-sm text-destructive mt-1">{errors.visibility_cost}</p>
                            {/if}
                        </div>           
                        
                        <div class="flex items-center justify-between pt-3 border-t border-border">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-mono">Visible to players</span>
                                <span class="text-xs text-secondary-foreground">Toggle data visibility for players</span>
                            </div>
                            <input 
                                class="kt-switch" 
                                type="checkbox" 
                                id="is_visible" 
                                checked={formData.is_visible}
                                on:change={(e) => {
                                    formData.is_visible = e.target.checked;
                                }}
                            />
                        </div>
                        {#if errors.is_visible}
                            <p class="text-sm text-destructive mt-1">{errors.is_visible}</p>
                        {/if}
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button id="cancel-button" type="button" class="kt-btn kt-btn-outline flex-1" data-kt-drawer-dismiss="true">
                            Cancel
                        </button>
                        <button type="submit" class="kt-btn kt-btn-primary flex-1">
                            {isEditMode ? 'Update Gameweek' : 'Create Gameweek'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout> 
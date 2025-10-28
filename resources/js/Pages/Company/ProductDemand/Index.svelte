<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs - make reactive to include product name
    $: breadcrumbs = [
        {
            title: 'Products',
            url: route('company.products.index'),
            active: false
        },
        {
            title: 'Demand Management',
            url: route('company.product-demand.index'),
            active: !currentProduct
        },
        ...(currentProduct ? [{
            title: currentProduct.name,
            url: route('company.product-demand.index', { product_id: currentProduct.id }),
            active: true
        }] : [])
    ];
    
    const pageTitle = 'Product Demand Management';

    // Props from Inertia
    export let productDemands = [];
    export let product = null;
    
    // Reactive variables
    let currentProduct = product;
    let demandData = productDemands || [];
    let loading = false;
    let showTable = true; // Toggle between chart and table view
    let chart = null;

    // Product selector
    let selectedProductId = currentProduct?.id || '';
    let productSelectComponent;



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
            const response = await fetch(route('company.product-demand.index', { 
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

    // Initialize on mount
    onMount(async () => {
        // Initialize menus after DOM is ready
        await tick();
        if (window.KTMenu) {
            window.KTMenu.init();
        }
        
        // If we have a product from Inertia props, set it up
        if (currentProduct) {
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

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Product Demand Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        View market demand data and pricing for your researched products
                    </p>
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
                                                url: route('company.products.index'),
                                                dataType: 'json',
                                                delay: 300,
                                                data: function(params) {
                                                    return {
                                                        search: params.term,
                                                        perPage: 100
                                                    };
                                                },
                                                processResults: function(data) {
                                                    return {
                                                        results: data.companyProducts.map(companyProduct => ({
                                                            id: companyProduct.product.id,
                                                            text: `${companyProduct.product.name}`,
                                                            name: companyProduct.product.name,
                                                            type: companyProduct.product.type,
                                                            type_name: companyProduct.product.type_name
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
                                        No data to see the chart
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
                                        No data to see the table
                                    </p>
                                </div>
                            {:else}
                                <div class="kt-scrollable-x-auto">
                                    <table class="kt-table kt-table-border">
                                        <thead>
                                            <tr>
                                                <th>Gameweek</th>
                                                <th>Min Demand</th>
                                                <th>Max Demand</th>
                                                <th>Market Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {#each demandData.sort((a, b) => b.gameweek - a.gameweek) as demand}
                                                <tr>
                                                    <td>
                                                        <span class="font-medium text-mono">Week {demand.gameweek}</span>
                                                    </td>
                                                    <td>{demand.min_demand}</td>
                                                    <td>{demand.max_demand}</td>
                                                    <td>{demand.market_price} DZD</td>   
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
</CompanyLayout> 
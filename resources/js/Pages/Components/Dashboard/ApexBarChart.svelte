<script>
    import { onMount, onDestroy } from 'svelte';
    import ApexCharts from 'apexcharts';

    export let title = '';
    export let subtitle = '';
    export let series = [];
    export let categories = [];
    export let height = 350;
    export let colors = ['#8b5cf6', '#f59e0b', '#ef4444', '#10b981'];
    export let loading = false;
    export let horizontal = false;

    let chartContainer;
    let chart;

    const defaultOptions = {
        chart: {
            type: 'bar',
            height: height,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: horizontal,
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        xaxis: {
            categories: categories,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: '#6b7280'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    colors: '#6b7280'
                },
                formatter: function(value) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'DZD',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                }
            }
        },
        colors: colors,
        dataLabels: {
            enabled: false
        },
        grid: {
            strokeDashArray: 4,
            borderColor: '#e5e7eb'
        },
        legend: {
            position: 'top',
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif'
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'DZD',
                        minimumFractionDigits: 2
                    }).format(value);
                }
            }
        }
    };

    function createChart() {
        if (chart) {
            chart.destroy();
        }

        const options = {
            ...defaultOptions,
            series: series,
            xaxis: {
                ...defaultOptions.xaxis,
                categories: categories
            }
        };

        chart = new ApexCharts(chartContainer, options);
        chart.render();
    }

    function updateChart() {
        if (chart) {
            chart.updateOptions({
                series: series,
                xaxis: {
                    categories: categories
                }
            });
        }
    }

    onMount(() => {
        if (!loading && series.length > 0) {
            createChart();
        }
    });

    onDestroy(() => {
        if (chart) {
            chart.destroy();
        }
    });

    // Reactive updates
    $: if (chart && !loading && series.length > 0) {
        updateChart();
    }

    $: if (!chart && !loading && series.length > 0 && chartContainer) {
        createChart();
    }
</script>

<div class="kt-card h-full">
    <div class="kt-card-header">
        <h3 class="kt-card-title">
            {title}
        </h3>
        {#if subtitle}
            <div class="kt-card-subtitle">
                {subtitle}
            </div>
        {/if}
    </div>
    
    <div class="kt-card-content">
        {#if loading}
            <div class="flex items-center justify-center" style="height: {height}px;">
                <div class="kt-skeleton w-full h-full rounded"></div>
            </div>
        {:else}
            <div bind:this={chartContainer}></div>
        {/if}
    </div>
</div>

<script>
    import { onMount, onDestroy } from 'svelte';
    import ApexCharts from 'apexcharts';

    export let title = '';
    export let subtitle = '';
    export let series = [];
    export let labels = [];
    export let height = 350;
    export let colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    export let loading = false;

    let chartContainer;
    let chart;

    const defaultOptions = {
        chart: {
            type: 'donut',
            height: height
        },
        labels: labels,
        colors: colors,
        legend: {
            position: 'bottom',
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif'
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return Math.round(val) + '%';
            }
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
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                return new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: 'DZD',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(total);
                            }
                        }
                    }
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
            labels: labels
        };

        chart = new ApexCharts(chartContainer, options);
        chart.render();
    }

    function updateChart() {
        if (chart) {
            chart.updateOptions({
                series: series,
                labels: labels
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

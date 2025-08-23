<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';

    // Props passed from controller
    export let events;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Events',
            url: route('admin.events.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.events.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Game Events';

    // Modal state
    let showConfirmModal = false;
    let selectedEvent = null;
    let running = false;

    // Group events by category
    $: eventsByCategory = events.reduce((acc, event) => {
        if (!acc[event.category]) {
            acc[event.category] = [];
        }
        acc[event.category].push(event);
        return acc;
    }, {});

    // Open confirmation modal
    function openConfirmModal(event) {
        selectedEvent = event;
        showConfirmModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#confirm_event_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close confirmation modal
    function closeConfirmModal() {
        showConfirmModal = false;
        selectedEvent = null;
        running = false;
        
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#confirm_event_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
    }

    // Run selected event
    async function runEvent() {
        if (!selectedEvent || running) return;

        running = true;
        try {
            router.post(route('admin.events.run'), {
                event: selectedEvent.key
            }, {
                onError: (errors) => {
                    running = false;
                    if (errors.event) {
                        showToast(errors.event, 'error');
                    } else {
                        showToast('Error running event. Please try again.', 'error');
                    }
                },
                onSuccess: () => {
                    running = false;
                    closeConfirmModal();
                }
            });
        } catch (error) {
            console.error('Error running event:', error);
            running = false;
        }
    }

    // Get impact color class
    function getImpactColorClass(impact) {
        switch (impact) {
            case 'positive':
                return 'border-green-200 bg-green-50';
            case 'negative':
                return 'border-red-200 bg-red-50';
            default:
                return 'border-blue-200 bg-blue-50';
        }
    }

    // Get impact badge class
    function getImpactBadgeClass(impact) {
        switch (impact) {
            case 'positive':
                return 'kt-badge-success';
            case 'negative':
                return 'kt-badge-destructive';
            default:
                return 'kt-badge-primary';
        }
    }

    // Flash message handling
    export let success;
    export let error;

    $: if (success) {
        showToast(success, 'success');
    }

    $: if (error) {
        showToast(error, 'error');
    }
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
                    <h1 class="text-2xl font-bold text-mono">Game Events</h1>
                    <p class="text-sm text-secondary-foreground">
                        Trigger global events that affect all players in the business game
                    </p>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="kt-card bg-warning/10 border-warning/20">
                <div class="kt-card-body p-4">
                    <div class="flex items-start gap-3">
                                                 <i class="fa-solid fa-info-circle text-warning text-xl mt-0.5"></i>
                        <div class="space-y-2">
                            <h5 class="font-medium text-warning">Important Notice</h5>
                            <div class="text-sm text-warning/80 space-y-1">
                                <p>• Events affect all companies and players in the game immediately</p>
                                <p>• Some events cannot be reversed, so use them carefully</p>
                                <p>• Events will send notifications to all players about the changes</p>
                                <p>• Consider the game balance and player experience before triggering events</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events by Category -->
            {#each Object.entries(eventsByCategory) as [category, categoryEvents]}
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">{category} Events</h4>
                        <div class="flex items-center gap-2">
                            <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                {categoryEvents.length} events
                            </span>
                        </div>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {#each categoryEvents as event}
                                <div class="kt-card border {getImpactColorClass(event.impact)} hover:shadow-md transition-shadow cursor-pointer" 
                                     role="button" 
                                     tabindex="0"
                                     on:click={() => openConfirmModal(event)}
                                     on:keydown={(e) => e.key === 'Enter' && openConfirmModal(event)}>
                                    <div class="kt-card-body p-4">
                                        <div class="flex items-start gap-3">
                                                                                         <div class="flex-shrink-0">
                                                 <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                                     <i class="{event.icon} text-lg {event.impact === 'positive' ? 'text-green-600' : event.impact === 'negative' ? 'text-red-600' : 'text-blue-600'}"></i>
                                                 </div>
                                             </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <h5 class="font-medium text-mono text-sm">{event.name}</h5>
                                                    <span class="kt-badge kt-badge-sm {getImpactBadgeClass(event.impact)}">
                                                        {event.impact}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-muted-foreground leading-relaxed">
                                                    {event.description}
                                                </p>
                                                                                                 <div class="mt-3">
                                                     <button 
                                                         class="kt-btn kt-btn-xs kt-btn-outline kt-btn-primary w-full"
                                                         on:click|stopPropagation={() => openConfirmModal(event)}
                                                     >
                                                         <i class="fa-solid fa-rocket text-xs"></i>
                                                         Trigger Event
                                                     </button>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                </div>
            {/each}
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#confirm_event_modal" aria-label="Toggle event confirmation modal"></button>

    <!-- Event Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="confirm_event_modal">
        <div class="kt-modal-content max-w-[600px] top-[10%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Event Execution</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#confirm_event_modal"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-x"
                        aria-hidden="true"
                    >
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="kt-modal-body">
                {#if selectedEvent}
                    <div class="space-y-4">
                        <!-- Event Info -->
                        <div class="flex items-center gap-4">
                                                         <div class="flex-shrink-0">
                                 <div class="w-16 h-16 rounded-lg {getImpactColorClass(selectedEvent.impact)} flex items-center justify-center">
                                     <i class="{selectedEvent.icon} text-2xl {selectedEvent.impact === 'positive' ? 'text-green-600' : selectedEvent.impact === 'negative' ? 'text-red-600' : 'text-blue-600'}"></i>
                                 </div>
                             </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-mono">{selectedEvent.name}</h4>
                                    <span class="kt-badge kt-badge-sm {getImpactBadgeClass(selectedEvent.impact)}">
                                        {selectedEvent.impact}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground mb-2">{selectedEvent.description}</p>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    {selectedEvent.category}
                                </span>
                            </div>
                        </div>

                        <!-- Event Impact Warning -->
                        <div class="kt-card {selectedEvent.impact === 'negative' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'}">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                                                         <i class="fa-solid fa-triangle-exclamation text-{selectedEvent.impact === 'negative' ? 'red' : 'yellow'}-600 text-xl mt-0.5"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-{selectedEvent.impact === 'negative' ? 'red' : 'yellow'}-800">
                                            {selectedEvent.impact === 'negative' ? 'Negative Impact Event' : 'Game-Changing Event'}
                                        </h5>
                                        <div class="text-sm text-{selectedEvent.impact === 'negative' ? 'red' : 'yellow'}-700 space-y-1">
                                            <p>• This event will affect all players immediately</p>
                                            <p>• All companies will receive notifications about this change</p>
                                            {#if selectedEvent.impact === 'negative'}
                                                <p>• This event may negatively impact player experience</p>
                                                <p>• Consider the timing and current game state</p>
                                            {:else}
                                                <p>• This event will change the game dynamics for all players</p>
                                                <p>• Make sure this aligns with your intended game progression</p>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground text-center p-4 bg-muted/30 rounded">
                            Are you sure you want to execute <strong>"{selectedEvent.name}"</strong>?<br>
                            This action cannot be undone and will affect all players in the game.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#confirm_event_modal"
                        on:click={closeConfirmModal}
                        disabled={running}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn {selectedEvent?.impact === 'negative' ? 'kt-btn-destructive' : 'kt-btn-primary'}"
                        on:click={runEvent}
                        disabled={running}
                    >
                                                 {#if running}
                             <i class="fa-solid fa-spinner animate-spin text-sm"></i>
                             Executing...
                         {:else}
                             <i class="fa-solid fa-rocket text-sm"></i>
                             Execute Event
                         {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
<script>
    import { router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    // Form data
    let form = {
        email: '',
        password: '',
        remember: false
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Password visibility toggle
    let showPassword = false;

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        router.post(route('auth.login'), form, {
            onError: (err) => {
                errors = err;
                loading = false;
            },
            onFinish: () => {
                loading = false;
            }
        });
    }

    // Toggle password visibility
    function togglePassword() {
        showPassword = !showPassword;
    }



    onMount(() => {
        // Initialize theme mode
        const defaultThemeMode = 'light';
        let themeMode;

        if (localStorage.getItem('kt-theme')) {
            themeMode = localStorage.getItem('kt-theme');
        } else {
            themeMode = defaultThemeMode;
        }

        document.documentElement.classList.add(themeMode);
    });
</script>

<svelte:head>
    <title>Business Game - Sign In</title>
    
    <!-- Page -->
    <style>
        .branded-bg {
            background-image: url('/assets/images/auth-page-background.png');
        }
        .dark .branded-bg {
            background-image: url('/assets/images/auth-page-background-dark.png');
        }
    </style>
</svelte:head>

<div class="grid lg:grid-cols-2 grow" style="height: 100vh;">
    <!-- Left Column - Login Form -->
    <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
        <div class="kt-card max-w-[370px] w-full">
            <form on:submit|preventDefault={handleSubmit} class="kt-card-content flex flex-col gap-5 p-10">
                <div class="text-center mb-2.5">
                    <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                        Sign in
                    </h3>
                    <div class="flex items-center justify-center font-medium">
                        <span class="text-sm text-secondary-foreground me-1.5">
                            Welcome to Business Game
                        </span>
                    </div>
                </div>

                <!-- Email Input -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="email">
                        Email
                    </label>
                    <input 
                        id="email"
                        name="email"
                        type="email" 
                        class="kt-input {errors.email ? 'kt-input-error' : ''}" 
                        placeholder="email@email.com" 
                        bind:value={form.email}
                        disabled={loading}
                    />
                    {#if errors.email}
                        <span class="text-sm text-destructive">{errors.email}</span>
                    {/if}
                </div>

                <!-- Password Input -->
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between gap-1">
                        <label class="kt-form-label font-normal text-mono" for="password">
                            Password
                        </label>
                    </div>
                    <div class="kt-input {errors.password ? 'kt-input-error' : ''}" data-kt-toggle-password="true">
                        <input 
                            id="password"
                            name="password"
                            type={showPassword ? 'text' : 'password'} 
                            placeholder="Enter Password" 
                            bind:value={form.password}
                            disabled={loading}
                        />
                        <button 
                            type="button"
                            class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" 
                            on:click={togglePassword}
                            disabled={loading}
                        >
                            {#if showPassword}
                                <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                            {:else}
                                <i class="ki-filled ki-eye text-muted-foreground"></i>
                            {/if}
                        </button>
                    </div>
                    {#if errors.password}
                        <span class="text-sm text-destructive">{errors.password}</span>
                    {/if}
                </div>

                <!-- Remember Me Checkbox -->
                <label class="kt-label">
                    <input 
                        class="kt-checkbox kt-checkbox-sm" 
                        name="remember" 
                        type="checkbox" 
                        bind:checked={form.remember}
                        disabled={loading}
                    />
                    <span class="kt-checkbox-label">
                        Remember me
                    </span>
                </label>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="kt-btn kt-btn-primary flex justify-center grow"
                    disabled={loading}
                >
                    {#if loading}
                        <i class="ki-outline ki-loading text-base animate-spin me-2"></i>
                        Signing In...
                    {:else}
                        Sign In
                    {/if}
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column - Branded Background -->
    <div class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg">
        <div class="flex flex-col p-8 lg:p-16 gap-4">
            <a href="/">
                <img class="h-[91px] max-w-none" src="/assets/images/logo.png" alt="Business Game Logo" style="width: 91px; height: 91px;"/>
            </a>
            <div class="flex flex-col gap-3">
                <h3 class="text-2xl font-semibold text-mono">
                    Business Game Portal
                </h3>
                <div class="text-base font-medium text-secondary-foreground">
                    A comprehensive business simulation platform
                    <br/>
                    providing
                    <span class="text-mono font-semibold">
                        immersive learning experiences
                    </span>
                    for
                    students and professionals.
                </div>
            </div>
        </div>
    </div>
</div> 
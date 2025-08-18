<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Business Game</title>
        <!-- Favicon -->
        <link href="/assets/images/logo.png" rel="shortcut icon"/>

        <!-- Metronic CSS -->
        <link href="/assets/vendors/apexcharts/apexcharts.css" rel="stylesheet"/>
        <link href="/assets/vendors/keenicons/styles.bundle.css" rel="stylesheet"/>
        <link href="/assets/css/theme.css" rel="stylesheet"/>
        <link href="/assets/css/main.css" rel="stylesheet"/>

        <!-- Font Awesome CSS -->
        <link href="/assets/vendors/fontawesome/css/fontawesome.css" rel="stylesheet" />
        <link href="/assets/vendors/fontawesome/css/solid.css" rel="stylesheet" />
        <link href="/assets/vendors/fontawesome/css/regular.css" rel="stylesheet" />
      
        <!-- Select2 CSS -->
        <link href="/assets/vendors/select2/css/select2.min.css" rel="stylesheet" />

        <!-- Flatpickr CSS -->
        <link href="/assets/vendors/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

        <!-- FullCalendar CSS -->
        <link href="/assets/vendors/fullcalendar/main.min.css" rel="stylesheet" type="text/css" />
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="antialiased flex h-full text-base text-foreground bg-background [--header-height:78px]">
      <!-- Theme Mode -->
        <script>
          document.documentElement.classList.add('light');
        </script>
        <!-- End of Theme Mode -->
        @inertia
        <!-- Metronic JavaScript -->
        <script src="/assets/js/core.bundle.js"></script>
        <script src="/assets/vendors/apexcharts/apexcharts.min.js"></script>
        <script src="/assets/vendors/ktui/ktui.min.js"></script>
        <script src="/assets/vendors/clipboard/clipboard.min.js"></script>
        <!-- jQuery -->
        <script src="/assets/vendors/jquery/jquery.min.js"></script>
        <!-- Select2 JavaScript -->
        <script src="/assets/vendors/select2/js/select2.min.js"></script>
        <!-- Flatpickr JavaScript -->
        <script src="/assets/vendors/flatpickr/flatpickr.min.js"></script>   
        <!-- FullCalendar JavaScript -->
        <script src="/assets/vendors/fullcalendar/main.min.js"></script>
        <!-- Metronic Layout Scripts -->
        <script src="/assets/js/widgets/general.js"></script>
    </body>
</html>
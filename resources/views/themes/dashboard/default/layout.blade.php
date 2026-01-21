<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ setting('site_name') }} - {{ $page_title ?? 'Home' }}</title>
    <meta name="description" content="{{ setting('meta_description') }}">
    <meta name="keywords" content="{{ setting('meta_keywords') }}">

    <!--Favicons-->
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/favicons/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/favicons/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/favicons/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/favicons/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/favicons/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/favicons/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/favicons/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/favicons/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicons/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('assets/favicons/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('assets/favicons/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Icons -->
    <link href="{{ dashboard_assets('js/plugins/nucleo/css/nucleo.css') }}" rel="stylesheet">
    <link href="{{ dashboard_assets('js/plugins/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    <!-- Argon CSS -->
    <link type="text/css" href="{{ dashboard_assets('css/argon-dashboard.css') }}" rel="stylesheet">

    @if(setting('cookie_consent_status') === 'yes')
    <!--Cookie Consent-->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css" />
    @endif
    @livewireStyles
    @stack('head_styles')
    {!! setting('header_codes') !!}
    @if(setting('custom_css_dashboard'))
        <style>
            {!! setting('custom_css_dashboard') !!}
        </style>
    @endif
</head>

<body>

<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
                aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Brand -->
        <a class="navbar-brand pt-0" href="{{ route('account') }}">
            <img src="{{ dashboard_assets('img/brand/blue.png') }}" class="navbar-brand-img" alt="...">
        </a>
        <!-- User -->
        <ul class="nav align-items-center d-md-none">
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">
                    <div class="media align-items-center">
              <span class="avatar avatar-sm rounded-circle">
                <img alt="Image placeholder" src="{{ dashboard_assets('img/theme/sketch.jpg') }}">
              </span>
                    </div>
                </a>
                @include('themes.dashboard.default.partials.user-dropdown-top-menu')
            </li>
        </ul>
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="#">
                            <img src="{{ dashboard_assets('img/brand/blue.png') }}">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse"
                                data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false"
                                aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            @include('themes.dashboard.default.partials.sidebar-menu')
        </div>
    </div>
</nav>
<div class="main-content">
    <!-- Navbar -->
    <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block"
               href="{{ route('index') }}">Home</a>
            <!-- User -->
            <ul class="navbar-nav align-items-center d-none d-md-flex">
                <li class="nav-item dropdown">
                    <a class="nav-link pr-0" href="{{ route('account') }}" role="button" data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false">
                        <div class="media align-items-center">
                            <span class="avatar avatar-sm rounded-circle">
                              <img alt="Image placeholder" src="{{ dashboard_assets('img/theme/sketch.jpg') }}">
                            </span>
                            <div class="media-body ml-2 d-none d-lg-block">
                                <span class="mb-0 text-sm  font-weight-bold">{{ auth()->user()->wallet }}</span>
                            </div>
                        </div>
                    </a>
                    @include('themes.dashboard.default.partials.user-dropdown-top-menu')
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
    @yield('header_content')

    <div class="container-fluid mt--7">

        @yield('content')

        <!-- Footer -->
        <footer class="footer">
            <div class="row align-items-center justify-content-xl-between">
                <div class="col-xl-6">
                    <div class="copyright text-center text-xl-left text-muted">
                        &copy; {{ date('Y') }} <a href="{{ route('index') }}" class="font-weight-bold ml-1">{{ setting('site_name') }}</a>. All rights reserved.
                    </div>
                </div>
                <div class="col-xl-6">
                    <ul class="nav nav-footer justify-content-center justify-content-xl-end">
                        <li class="nav-item">
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#tosModal" class="nav-link">Terms of Service</a>
                        </li>
                        <li class="nav-item">
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#privacyModal" class="nav-link">Privacy Policy</a>
                        </li>
                    </ul>
                </div>
                <div class="col-xl-12">
                    @include('themes.dashboard.default.partials.social')
                </div>
            </div>
        </footer>
    </div>
</div>

@livewire('deposit')
@livewire('withdrawal')

@include('themes.dashboard.default.partials.tos-modal')
@include('themes.dashboard.default.partials.privacy-modal')
@include('themes.dashboard.default.partials.faqs-modal')

<!-- Core -->
<script src="{{ dashboard_assets('js/plugins/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ dashboard_assets('js/plugins/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

<!-- Argon JS -->
<script src="{{ dashboard_assets('js/argon-dashboard.js') }}"></script>
<script src="{{ dashboard_assets('js/custom.js') }}"></script>

@if(setting('cookie_consent_status') === 'yes')
    <!--Cookie Consent-->
    <script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
    <script>
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    "background": "{{ setting('cookie_consent_popup_background') }}",
                    "text": "{{ setting('cookie_consent_popup_text_color') }}"
                },
                "button": {
                    @if(setting('cookie_consent_layout') === 'wire')
                    "background": "transparent",
                    "border": "{{ setting('cookie_consent_button_border_color') }}",
                    @else
                    "background": "{{ setting('cookie_consent_button_background') }}",
                    @endif
                    "text": "{{ setting('cookie_consent_button_text_color') }}"
                }
            },
            "position": "{{ setting('cookie_consent_position') }}",
            @if(setting('cookie_consent_position') === 'top-static')
            "static": true,
            @else
            "static": false,
            @endif
                    @if(setting('cookie_consent_layout') !== 'wire')
            "theme": "{{ setting('cookie_consent_layout') }}"
            @endif
        });
    </script>
@endif
@livewireScripts
@stack('footer_scripts')
{!! setting('footer_codes') !!}
</body>

</html>
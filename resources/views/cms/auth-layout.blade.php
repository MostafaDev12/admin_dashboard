<!doctype html>
<html lang="en" data-topbar="light"><head><meta charset="utf-8"><title>@yield('title') | Admin & Dashboard</title><meta name="viewport" content="width=device-width, initial-scale=1.0">@include('layouts.head-css')</head><body>
@yield('content')
<script src="{{ asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@yield('script')
@yield('script-bottom')
</body></html>

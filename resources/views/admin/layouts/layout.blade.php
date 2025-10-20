<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>


    @include('admin.layouts.header')
    @yield('page_css')
</head>
<style>
    .buttonBar.left {
        display: none;
    }

    .alert-banner {
        background-color: #48975b;
        /* Matches the blue background */
        color: white;
        font-weight: 500;
        border: none;
    }

    .icon-container {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px;
        border-radius: 5px;
    }

    .btn-close {
        filter: invert(1);
        /* Makes close icon white */
        opacity: 1;
    }
</style>

<body>

    @yield('content')





    @include('admin.layouts.scripts')
    @yield('page_js')
</body>

</html>

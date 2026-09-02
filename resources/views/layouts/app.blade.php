<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="language" content="en-us" />
    <meta name="description" content="Peterborough Fish & Game Association gun club in Peterborough Ontario Canada" />
    <meta name="keywords" content="handgun, rifle, archery, indoor range, outdoor range" />
    <meta name="robots" content="all" />
    <meta name="author" content="Bryan McKellar" />
    <meta name="copyright" content="2018, PFGA" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Peterborough Fish & Game Association') }}</title>
    <link rel="shortcut icon" href="{{ asset('images/pfgalogo.ico') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/pfga.css') }}" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    @include('partials.header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('partials.nav')
            </div>
        </div>

        <main class="py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
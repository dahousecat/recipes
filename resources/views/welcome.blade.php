<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Smoothie recipes</title>
        <meta name="description" content="Smoothie recipes with nutrients" />
        <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
    </head>
    <body>
        <div id="root"></div>
        <div id="overlay"></div>
    </body>

    {{--<script type="text/javascript" src="/js/jquery-2.1.4.min.js"></script>--}}
    {{--<script type="text/javascript" src="/js/responsive.min.js"></script>--}}
    {{--<script type="text/javascript" src="/js/nav.js"></script>--}}

    <script type="text/javascript" src="{{ mix('js/app.js') }}"></script>

</html>

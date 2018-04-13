<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel React application</title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.12/semantic.min.css"></link>
    <link href="{{url(mix('css/app.css'))}}" rel="stylesheet" type="text/css">
</head>
<body>
{{--<h2 style="text-align: center"> Laravel and React application </h2>--}}
<div id="root"></div>
<script src="{{url(mix('js/app.js'))}}" ></script>
</body>
</html>
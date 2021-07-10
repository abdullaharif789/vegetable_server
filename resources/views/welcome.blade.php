<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Everyday Fresh Food</title>
        <style>
            * {
                margin: 0px;
            }
            .content{
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        </style>
    </head>
    <body>
        <div class="content" style="background-image: url({{asset('dot.png')}});">
            <img src="{{asset('logo.png')}}">
        </div>
    </body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ App::environment('local') ? '/css/admin.css' : elixir('css/admin.css') }}">

    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1">
    <title>Admin Panel :: StopHab</title>
</head>

<body>
<div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a href="{{url('admin')}}" class="navbar-brand">StopHab :: CP</a>
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="orgz">Организации <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="orgz">
                        <li><a href="{{url('admin/organization')}}">Все организации</a></li>
                        <li><a href="{{url('admin/organization/create')}}">Создать организацию</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="misc">misc <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="misc">
                        <li><a href="{{url('admin/status')}}">статусы организаций</a></li>
                        <li><a href="{{url('admin/opf')}}">ОПФ (организационно-правовая форма)</a></li>
                        <li><a href="{{url('admin/type')}}">Типы</a></li>
                        <li><a href="{{url('admin/city')}}">Города</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="preloader"><img src="/images/preloader.gif"></div>

@yield('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="{{ App::environment('local') ? '/js/admin.js' : elixir('js/admin.js') }}"></script>
<script src="{{ App::environment('local') ? '/js/admin_browserify.js' : elixir('js/admin_browserify.js') }}"></script>
<script>window._token = '{{csrf_token()}}';</script>
@yield('scripts')

</body>
</html>
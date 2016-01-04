<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/admin">
    <title>Admin Panel :: StopHab</title>
</head>

<body>
<div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a href="" class="navbar-brand">StopHab :: CP</a>
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
                        <li><a href="organization/list">Все организации</a></li>
                        <li><a href="organization/create">Создать организацию</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="misc">misc <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="misc">
                        <li><a href="">статусы организаций</a></li>
                        <li><a href="">ОПФ (организационно-правовая форма)</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

@yield('content')

<link rel="stylesheet" href="{{ App::environment('local') ? '/css/admin.css' : elixir('css/admin.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="/js/admin.js"></script>

</body>
</html>
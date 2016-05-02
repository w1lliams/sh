<!DOCTYPE html>
<html lang="en">
<head>
    <link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="{{ App::environment('local') ? '/css/site.css' : elixir('css/site.css') }}">

    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1">
    <meta name="_token" content="{{csrf_token()}}">
    <title>StopHab</title>
</head>

<body>
<div class="g-head">

  <div class="container">
    <div class="logo-icon"><i class="sprite logo"></i></div>
    <div class="info">
      <div class="title">СтопХаб</div>
      <p>ГО "Громадський рух СтопХаб"</p>
    </div>
    <div class="social-links">
      <a href="#"><i class="sprite facebook"></i></a>
      <a href="#"><i class="sprite vk"></i></a>
    </div>
  </div>
</div>
@yield('content')

<footer class="footer">
  <div class="container">
    <div class="social-links">
      <a href="#"><i class="sprite facebook"></i></a>
      <a href="#"><i class="sprite vk"></i></a>
    </div>

    <div class="info">
      <p>О "Громадський рух "СтопХаб". Для зв'язку з нами використовуйте <a href="#">форму зворотнього зв'язку</a>.</p>
      <p>СтопХаб  © 2015. Всі права захищені.</p>
    </div>

    <div class="logo-icon"><i class="sprite logo"></i></div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="{{ App::environment('local') ? '/js/site.js' : elixir('js/site.js') }}"></script>
<script src="{{ App::environment('local') ? '/js/site_browserify.js' : elixir('js/site_browserify.js') }}"></script>
@yield('scripts')

</body>
</html>

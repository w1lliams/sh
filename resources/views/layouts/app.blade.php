<!DOCTYPE html>
<html lang="en">
<head>
    <link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="{{ App::environment('local') ? '/css/site.css' : elixir('css/site.css') }}">

    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1">
    <meta name="_token" content="{{csrf_token()}}">
    <title>Громадський рух "СтопХаб" - Дізнайся все про чиновника! Майно, доходи, бізнес посадовців. Відгуки про чиновників.</title>
</head>

<body>
<div class="g-head">

  <div class="container">
    <div class="logo-icon"><a href=/><i class="sprite logo"></i></a></div>
    <div class="info">
      <div class="title">СтопХаб</div>
      <p>ГО "Громадський рух СтопХаб"</p>
    </div>
    <div class="social-links">
      <a href="http://www.facebook.com/stophab" target=_blank><i class="sprite facebook"></i></a>
      <a href="http://vk.com/stophab" target=_blank><i class="sprite vk"></i></a>
    </div>
  </div>
</div>
@yield('content')

<footer class="footer">
  <div class="container">
    <div class="social-links">
      <a href="http://www.facebook.com/stophab" target=_blank><i class="sprite facebook"></i></a>
      <a href="http://vk.com/stophab" target=_blank><i class="sprite vk"></i></a>
    </div>

    <div class="info">
      <p>ГО "Громадський рух "СтопХаб". Для зв'язку з нами використовуйте <a style="text-decoration: underline !important; color: #ffffff;" href="/feedback">форму зворотнього зв'язку</a>.</p>
      <p>СтопХаб  © 2015-<?php echo date('Y') ?>. Всі права захищені.</p>
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

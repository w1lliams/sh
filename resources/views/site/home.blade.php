@extends('layouts.app')

@section('content')
<div class="home-page">
  <div class="search-block">
    <div class="container">
      <h1>Дізнайтесь ВСЕ про чиновника!</h1>
      <p>Громадський рух СТОПХАБ контролює діяльність та оприлюднює інформацію про чиновників, посадовців, лікарів, викладачів та всіх інших бюджетних працівників.</p>
      @include('site.parts.search')
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-9 news-block">
        <h3>Новини</h3>
        @for($i = 0; $i < 3; $i++)
          <div class="news">
            <div class="date">13.04.2016 17:42</div>
            <a href="#" class="title">Перелик Працивникив</a>
            <p>
              Приєднуйтесь до команди волонтерів Громадського руху "Стопхаб" і допомагайте у пошуку інформації, оприлюднення якої сприятиме відкритості та прозорості влади.
              <a href="#">Подивитися</a>
            </p>
          </div>
        @endfor
        <div class="more">
          <a href="#">показати ще</a>
        </div>
      </div>


      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

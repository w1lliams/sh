@extends('layouts.app')

@section('content')
<div class="home-page">
  <div class="search-block">
    <div class="container">
      <h1>Дізнайтесь ВСЕ про чиновника!</h1>
      <p>Громадський рух СТОПХАБ контролює діяльність та оприлюднює інформацію про чиновників, посадовців, лікарів, викладачів та всіх інших бюджетних працівників.</p>
      <form class="" action="" method="get">
        <div class="input-group input-group-lg">
          <input type="text" name="q" value="" placeholder="Кого шукаємо?" class="form-control">
          <div class="input-group-btn">
            <button type="submit" class="btn btn-primary">Шукати</button>
          </div>
        </div>
      </form>
      <p class="stat-text">Пошук серед 19711 чиновників та 153 організацій, <a href="#">показати всі</a></p>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-8 news-block">
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


      <div class="col-md-4 donate-block">
        <div class="author">
          <div class="image">
            <i class="sprite brel"></i>
          </div>
          <div class="info">
            <div class="name">Олександр Брель</div>
            <div class="position">керівник ГО "Стопхаб"</div>
          </div>
        </div>

        <div class="info">
          <p>Шановні відвідувачі!</p>
          <p>Приєднуйтесь до команди волонтерів Громадського руху "Стопхаб" і допомагайте у пошуку інформації, оприлюднення якої сприятиме відкритості та прозорості влади.</p>
          <a href="#" class="btn btn-success btn-block">Допомогти</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

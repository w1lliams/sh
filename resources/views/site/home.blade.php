@extends('layouts.app')

@section('content')
<div class="home-page">
  <div class="search-block">
    <div class="container">
      <h1>Дізнайтесь ВСЕ про чиновника!</h1>
      <p>Громадський рух СТОПХАБ оприлюднює інформацію про чиновників, посадовців, лікарів, викладачів та всіх інших бюджетних працівників.</p>
      @include('site.parts.search')
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-9 news-block">
        <h3>Новини</h3>

          <div class="news">
            <div class="date">12.07.2016 07:15</div>
            <a href="/worker/35166" class="title">Голованов Олександр Дмитрович</a>
            <p>
	      Управління архітектури та містобудування Одеської міської ради.<br>
              Додано інформацію про публікації в ЗМІ. 
              <a href="/worker/35166">Подивитися</a>
            </p>
          </div>

          <div class="news">
            <div class="date">11.07.2016 15:41</div>
            <a href="/organization/5752" class="title">Перелік працівників</a>
            <p>
	      Одеський дошкільний навчальний заклад "Ясла-садок" №43 Одеської міської ради Одеської області.<br>
              Додано перелік працівників станом на 01.07.2016 року. 
              <a href="/organization/5752">Подивитися</a>
            </p>
          </div>

          <div class="news">
            <div class="date">10.07.2016 07:15</div>
            <a href="/worker/36858" class="title">Сегеда Олена Михайлівна</a>
            <p>
	      Малиновський районний суд м. Одеси.<br>
              Додано інформацію про публікації в ЗМІ, доходи.
              <a href="/worker/36858">Подивитися</a>
            </p>
          </div>


        <div class="more">
          <a href="#">показати ще</a>
        </div>
      </div>


      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

<div class="g-search-form">
  <form id="search-form" action="" method="get">
    <div class="input-group input-group-lg">
      <input type="text" name="q" value="" placeholder="Кого шукаємо?" class="form-control" autocomplete="off">
      <div class="input-group-btn">
        <button type="submit" class="btn btn-primary">Шукати</button>
      </div>
    </div>

    <div class="results"></div>
  </form>
  <p class="stat-text">Пошук серед <b>{{$workersCount}}</b> чиновників та <b>{{$organizationCount}}</b> організацій, <a href="{{route('organizations')}}">показати&nbsp;всі</a></p>
</div>
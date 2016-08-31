@extends('layouts.app')

@section('head')
    <title>Громадський рух "СтопХаб" - Дізнайся все про чиновника! Майно, доходи, бізнес посадовців. Відгуки про чиновників.</title>
@endsection

@section('content')
<div class="organization-page">
  <div class="g-search-block">
    <div class="container">
      @include('site.parts.search')
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-9 content-block">

        <div class="info-block">
          <div class="icon">
            <i class="sprite tile organization"></i>
          </div>
          <div class="info">
            <h1>Всі органи, організації, підприємства</h1>
          </div>
        </div>

        @foreach($organizations as $organization)
              <div class="info-block">
                  <div class="icon"></div>
                  <div class="info">
                      <h5 class="mb-0"><a href="{{route('organization', $organization->id)}}">{{$organization->fullName}}</a></h5>
                      <p>
                          Всього працівників: {{$organization->snapshot->count}} (Станом на: {{$organization->snapshot->date->format('d.m.Y')}})
                          <br><a href="{{route('organization', $organization->id)}}">Показати працівників</a>
                      </p>
                  </div>
              </div>
        @endforeach

      {!! $organizations->render() !!}
      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

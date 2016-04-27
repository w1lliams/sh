@extends('layouts.app')

@section('content')
<div class="worker-page">
  <div class="g-search-block">
    <div class="container">
      @include('site.parts.search')
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-8 content-block">
        <div class="info-block">
          <div class="icon"></div>
          <div class="info">
            <h1>{{$worker->fio}}</h1>
            <div class="position  text-muted">
              {{$worker->position}}
            </div>

            <div class="organization  text-muted">
              {{$worker->organization->fullName}}, <a href="{{route('organization', $worker->organization->id)}}">вси працивники</a>
            </div>
          </div>
        </div>
      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

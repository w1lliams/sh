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
      <div class="col-md-9 content-block">
        <div class="info-block">
          <div class="icon">
            <i class="sprite tile worker"></i>
          </div>
          <div class="info">
            <h1>{{$worker->fio}}</h1>
            <div class="position  text-muted">
              {{$worker->position}}
              @if(!empty($worker->department))
                {{$worker->department}}
              @endif
              @if(!empty($worker->subDepartment))
                {{$worker->subDepartment}},
              @endif
            </div>

            <div class="organization  text-muted">
              {{$worker->organization->fullName}}, <a href="{{route('organization', $worker->organization->id)}}">всі працівники</a>
            </div>
          </div>
        </div>

        @if($worker->notes_count > 0)
              @if(count($worker->publications) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile news"></i>
                      </div>
                      <div class="info">
                          <h5>Публікації в ЗМІ</h5>
                          @each('site.parts.note', $worker->publications, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->finance) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile money"></i>
                      </div>
                      <div class="info">
                          <h5>Доходи</h5>
                          @each('site.parts.note', $worker->finance, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->realty) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile realty"></i>
                      </div>
                      <div class="info">
                          <h5>Нерухомість</h5>
                          @each('site.parts.note', $worker->realty, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->cars) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile cars"></i>
                      </div>
                      <div class="info">
                          <h5>Автомобілі</h5>
                          @each('site.parts.note', $worker->cars, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->law) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile law"></i>
                      </div>
                      <div class="info">
                          <h5>Справи в судах</h5>
                          @each('site.parts.note', $worker->law, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->business) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile business"></i>
                      </div>
                      <div class="info">
                          <h5>Підприємства та підприємництво</h5>
                          @each('site.parts.note', $worker->business, 'note')
                      </div>
                  </div>
              @endif

                  <a href="{{route('feedback', ['w' => $worker->id])}}">add feedback</a>
        @endif

      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

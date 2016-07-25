@extends('layouts.app')

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
            <h1>{{$organization->fullName}}</h1>
            <div class="small address text-muted">
              {{$organization->address}}
            </div>
          </div>
        </div>

        @if(!is_null($snapshot))
          <div class="info-block">
            <div class="icon">
              <i class="sprite tile workers"></i>
            </div>
            <div class="info">
              <h3>
                Перелік працівників
                <small>(станом на {{$snapshot->date->format('d.m.Y')}} року)</small>
              </h3>

              <div class="worker-list">
                @foreach($workers as $departmentName => $department)
                  @if($departmentName != 'main')
                    <div class="department-name">{{$departmentName}}</div>
                  @endif

                  @if(isset($department['workers']))
                      @foreach($department['workers'] as $worker)
                          <div class="worker @if($departmentName != 'main') department-worker @endif">
                              @if($worker->notes_count > 0)
                                  <i class="sprite info"></i>
                              @endif
                            <a href="{{route('worker', $worker->id)}}">{{$worker->fio}}</a>, {{$worker->position}}
                          </div>
                      @endforeach
                  @endif

                  @if(!empty($department['sub']))
                    @foreach($department['sub'] as $subDepartmentName => $subWorkers)
                      <div class="subdepartment-name">{{$subDepartmentName}}:</div>
                      @foreach($subWorkers as $worker)
                        <div class="worker subdepartment-worker">
                            @if($worker->notes_count > 0)
                                <i class="sprite info"></i>
                            @endif
                          <a href="{{route('worker', $worker->id)}}">{{$worker->fio}}</a>, {{$worker->position}}
                        </div>
                      @endforeach
                    @endforeach
                  @endif
                @endforeach
              </div>
            </div>
          </div>
        @else
          <div class="info-block">
            <div class="icon">
              <i class="sprite tile workers"></i>
            </div>
            <div class="info">
              <h3 class="mb-0">Перелік працівників</h3>
              <p>Очікується від розпорядника інформації...</p>
            </div>
          </div>
        @endif
      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

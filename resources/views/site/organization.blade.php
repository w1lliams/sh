@extends('layouts.app')

@section('head')
  <title>{{$organization->fullName}} - всі працівники: інформація, декларації, відгуки</title>
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

			<? 	// Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Плюсуем их к notes_count
				$worker->notes_count = $worker->notes_count + get_additional_notes_count($worker->fio);
			?>

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

			<? 	// Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Плюсуем их к notes_count
				$worker->notes_count = $worker->notes_count + get_additional_notes_count($worker->fio);
			?>
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
              <p>Очікується...</p>
            </div>
          </div>
        @endif


                @if(count($organization->organizations) > 0)
	        <div class="info-block">
	          <div class="icon">
	            <i class="sprite tile organization"></i>
	          </div>
	          <div class="info">
	            <h3 class="mb-0">Відокремлені підрозділи та структурні одиниці</h3>
                    @foreach($organization->organizations as $department)
<div class="listofanotherdepartments">
	 	    <a  href="{{url("organization/{$department->id}")}}">{{$department->fullName}}</a>
	            <div class="small address text-muted">{{$department->address}}</div>
</div>
                    @endforeach
        	  </div>
	        </div>
                @endif


      </div>

      @include('site.parts.donate')
      @include('site.parts.vkandfb')

    </div>
  </div>
</div>
@endsection

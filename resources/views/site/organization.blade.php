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

			<?
				// Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Плюсуем их к notes_count
				include_once resource_path().'/classes/base32.php';
				$WorkerRealty = array(); $WorkerCars = array(); $WorkerBusiness = array();
				$Base32fio = $base32->encode($worker->fio);
				$WorkerDataDirectory = public_path().'/data/'.$Base32fio; 
				if(is_dir($WorkerDataDirectory)) {
					$WorkerDataFiles = scandir($WorkerDataDirectory);
					foreach ($WorkerDataFiles as $file) {if (stripos($file, 'neruh_') === 0) array_push($WorkerRealty, $file); elseif (stripos($file, 'avto_') === 0) array_push($WorkerCars, $file); elseif (stripos($file, 'pidpr_') === 0) array_push($WorkerBusiness, $file);}
					if (count($WorkerRealty)>0)	$worker->notes_count++;
					if (count($WorkerCars)>0)	$worker->notes_count++;
					if (count($WorkerBusiness)>0)	$worker->notes_count++;
					}                                          
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

			<?
				// Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Плюсуем их к notes_count
				include_once resource_path().'/classes/base32.php';
				$WorkerRealty = array(); $WorkerCars = array(); $WorkerBusiness = array();
				$Base32fio = $base32->encode($worker->fio);
				$WorkerDataDirectory = public_path().'/data/'.$Base32fio; 
				if(is_dir($WorkerDataDirectory)) {
					$WorkerDataFiles = scandir($WorkerDataDirectory);
					foreach ($WorkerDataFiles as $file) {if (stripos($file, 'neruh_') === 0) array_push($WorkerRealty, $file); elseif (stripos($file, 'avto_') === 0) array_push($WorkerCars, $file); elseif (stripos($file, 'pidpr_') === 0) array_push($WorkerBusiness, $file);}
					if (count($WorkerRealty)>0)	$worker->notes_count++;
					if (count($WorkerCars)>0)	$worker->notes_count++;
					if (count($WorkerBusiness)>0)	$worker->notes_count++;
					}                                          
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
      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

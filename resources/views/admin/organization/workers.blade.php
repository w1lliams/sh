@extends('layouts.admin')
@section('content')
<div class="container page-workers">
    @include('admin.organization.menu')
    <h4>Список работников от {{$snapshot->date ? $snapshot->date->format('d.m.Y') : ''}} <small class="text-muted">(загружен {{$snapshot->created_at}})</small></h4>
    <ul class="workers-list">
        @foreach($workers as $departmentName => $department)
            <li class="department">
                @if(!empty($department['workers']))
                    <span class="department-name">{{$departmentName}}</span>
                    <ul>
                    @foreach($department['workers'] as $worker)
                        <li class="worker">
                            <span class="name" data-id="{{$worker->id}}">{{$worker->fio}}, {{$worker->position}}</span>
                            <a href="{{route('admin::worker_notes', $worker->id)}}" class="add-note"><i class="ion-ios-list-outline"></i></a>
                        </li>
                    @endforeach
                    </ul>
                @endif

                @if(!empty($department['sub']))
                    @foreach($department['sub'] as $subDepartmentName => $subWorkers)
                        <ul>
                            <li class="department">
                                <span class="department-name" data-sub="true">{{$subDepartmentName}}</span>
                                <ul>
                                @foreach($subWorkers as $worker)
                                    <li class="worker">
                                        <span class="name" data-id="{{$worker->id}}">{{$worker->fio}}, {{$worker->position}}</span>
                                        <a href="{{route('admin::worker_notes', $worker->id)}}"  class="add-note"><i class="ion-ios-list-outline"></i></a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        </ul>
                    @endforeach
                @endif
            </li>
        @endforeach
    </ul>
</div>

<!-- Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Добавление информации о сотруднике</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endsection
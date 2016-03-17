@extends('layouts.admin')

@section('content')
<div class="container-fluid page-organizations">
    <h4>
        <a class="pull-right" href="{{url('admin/organization/create')}}"><i class="ion-ios-plus-outline"></i></a>
        Организации
    </h4>

    <div class="jumbotron">
        <form action="" class="form-inline">
            <div class="form-group">
                {!! Form::label('status', 'Статус организации', ['class' => 'control-label']) !!}<br>
                {!! Form::select('status[]', $statuses->toArray(), request('status'), ['class' => 'multiselect', 'multiple']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('edrpou', 'ЄДРПОУ код', ['class' => 'control-label']) !!}<br>
                {!! Form::text('edrpou', request('edrpou'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('name', 'Название', ['class' => 'control-label']) !!}<br>
                {!! Form::text('name', request('name'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('type', 'Тип', ['class' => 'control-label']) !!}<br>
                {!! Form::select('type[]', $types->toArray(), request('type'), ['class' => 'multiselect', 'multiple']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('opf', 'ОПФ', ['class' => 'control-label']) !!}<br>
                {!! Form::select('opf[]', $opfs->toArray(), request('opf'), ['class' => 'multiselect', 'multiple']) !!}
            </div>
            <button type="submit" class="btn btn-primary">Фильтр</button>
            <a href="{{url('admin/organization')}}" class="btn btn-link">Сбросить</a>
        </form>
    </div>

    <div>Всего: {{count($organizations)}}</div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>№</th>
                <th>Статус</th>
                <th>ЕДРПОУ</th>
                <th>Название</th>
                <th>Тип</th>
                <th>ОПФ</th>
                <th>Город</th>
                <th>Адрес</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($organizations as $i => $organization)
                <tr>
                    <td>{{$i + 1}}</td>
                    <td>{{$organization->status->name or ''}}</td>
                    <td>{{$organization->edrpou}}</td>
                    <td>{{$organization->fullName}}</td>
                    <td>{{$organization->type->name or ''}}</td>
                    <td>{{$organization->opf->name or ''}}</td>
                    <td>{{$organization->city->name or '' }}</td>
                    <td>{{$organization->address}}</td>
                    <td width="40">
                        <div class="dropdown">
                            <div data-toggle="dropdown" class="option-btn" aria-haspopup="true" aria-expanded="false">
                                <i class="ion-android-more-vertical"></i>
                            </div>
                            <ul class="dropdown-menu">
                                <li><a href="{{url("admin/organization/{$organization->id}/edit")}}">Редактировать</a></li>
                                <li><a href="{{url("admin/organization/{$organization->id}/workers")}}">Работники</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
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
                {!! Form::label('chief', 'Руководитель', ['class' => 'control-label']) !!}<br>
                {!! Form::text('chief', request('chief'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('city', 'Город', ['class' => 'control-label']) !!}<br>
                {!! Form::select('city[]', $cities->toArray() + ['nocity' => 'Без города'], request('city'), ['class' => 'multiselect', 'multiple']) !!}
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

            <div><div class="show-all-departments">Развернуть все подразделения</div></div>
        </form>
    </div>

    <div>Всего: {{count($organizations)}}</div>
    <table class="table">
        <thead>
            <tr>
                <th>№</th>
                <th>Статус</th>
                <th>ЕДРПОУ</th>
                <th>Название</th>
                <th>Руководитель</th>
                <th>Тип</th>
                <th>ОПФ</th>
                <th>Город</th>
                <th>Адрес</th>
            </tr>
        </thead>
        <tbody>
            @foreach($organizations as $i => $organization)
                <tr class="organization">
                    <td>{{$i + 1}}</td>
                    <td>{{$organization->status->name or ''}}</td>
                    <td><a href="{{url("admin/organization/{$organization->id}/edit")}}">{{$organization->edrpou}}</a></td>
                    <td>
                        <a href="{{url("admin/organization/{$organization->id}/edit")}}">{{$organization->fullName}}</a>
                        @if(count($organization->organizations) > 0)
                            <div>
                                <div class="show-department-btn" data-organization="{{$organization->id}}">Показать подразделения ({{count($organization->organizations)}})</div>
                            </div>
                        @endif
                    </td>
                    <td>{{$organization->chief}}</td>
                    <td>{{$organization->type->name or ''}}</td>
                    <td>{{$organization->opf->name or ''}}</td>
                    <td>{{$organization->city->name or '' }}</td>
                    <td>{{$organization->address}}</td>
                </tr>

                @if(count($organization->organizations) > 0)
                    @foreach($organization->organizations as $department)
                        <tr class="department department{{$organization->id}}">
                            <td class="relation"></td>
                            <td>{{$organization->status->name or ''}}</td>
                            <td><a href="{{url("admin/organization/{$department->id}/edit")}}">{{$department->edrpou}}</a></td>
                            <td>
                                <a href="{{url("admin/organization/{$department->id}/edit")}}">{{$department->fullName}}</a>
                            </td>
                            <td>{{$department->chief}}</td>
                            <td>{{$department->type->name or ''}}</td>
                            <td>{{$organization->opf->name or ''}}</td>
                            <td>{{$department->city->name or '' }}</td>
                            <td>{{$department->address}}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
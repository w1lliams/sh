@extends('layouts.admin')

@section('content')
<div class="container page-organizations">
    <h4>
        <a class="pull-right" href="{{url('admin/organization/create')}}"><i class="ion-ios-plus-outline"></i></a>
        Организации
    </h4>

    <div class="jumbotron">
        <form action="" class="form-inline">
            <div class="row">
                <div class="form-group">
                    {!! Form::label('status', 'Статус организации', ['class' => 'control-label']) !!}<br>
                    {!! Form::select('status', ['' => 'Любой'] + $statuses->toArray(), old('status'), ['class' => 'multiselect']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('opf', 'ОПФ', ['class' => 'control-label']) !!}<br>
                    {!! Form::select('opf', ['' => 'Любой'] + $opfs->toArray(), old('opf'), ['class' => 'multiselect']) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    {!! Form::label('edrpou', 'ЄДРПОУ код', ['class' => 'control-label']) !!}<br>
                    {!! Form::text('edrpou', old('edrpou'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('name', 'Название', ['class' => 'control-label']) !!}<br>
                    {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Искать</button>
            <a href="{{url('admin/organization')}}" class="btn btn-link">Сбросить</a>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ЕДРПОУ</th>
                <th>Название</th>
                <th>Город</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{$organization->edrpou}}</td>
                    <td>
                        {{$organization->fullName}}
                        <br>
                        <small class="text-muted">{{$organization->opf->name}}</small>
                    </td>
                    <td>{{$organization->city->name or '' }}</td>
                    <td>{{$organization->status->name}}</td>
                    <td width="100">
                        <div class="actions">
                            <a href="{{url("admin/organization/{$organization->id}/edit")}}" class="fs20 btn-invisible">
                                <i class="ion-ios-compose-outline"></i>
                            </a>

                            <a href="{{url("admin/organization/{$organization->id}/workers")}}" class="fs20 btn-invisible">
                                <i class="ion-ios-list-outline"></i>
                            </a>

                            <form action="{{url("admin/organization/{$organization->id}/remove")}}" method="post">
                                {{csrf_field()}}
                                {{method_field('DELETE')}}
                                <button type="submit" class="fs20 btn-invisible">
                                    <i class="ion-ios-trash-outline"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $organizations->links() !!}
</div>
@endsection
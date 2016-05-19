@extends('layouts.admin')
@section('content')
<div class="container page-worker">
    <h3>{{$worker->fio}}</h3>

    @include('common.errors')
    <form class="jumbotron" action="{{route('admin::add_note', $worker->id)}}" method="post">
        {{csrf_field()}}
        <div class="form-group">
            <select name="type" class="form-control">
                <option value="publications">Публикации в СМИ</option>
                <option value="finance">Доходы</option>
                <option value="realty">Недвижимость</option>
                <option value="cars">Автомобили</option>
                <option value="law">Дела в судах</option>
                <option value="business">Предпринимательство</option>
            </select>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" name="text" placeholder="Текст">
        </div>
        <div class="form-group">
            <input type="url" class="form-control" name="url" placeholder="Ссылка">
        </div>
        <button type="submit" class="btn btn-primary">Добавить</button>
    </form>

    <h5>Публикации</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->publications, 'note')
    </ul>

    <h5>Доходы</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->finance, 'note')
    </ul>

    <h5>Недвижимость</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->realty, 'note')
    </ul>

    <h5>Автомобили</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->cars, 'note')
    </ul>

    <h5>Дела в судах</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->law, 'note')
    </ul>

    <h5>Предпринимательство</h5>
    <ul class="list-group">
        @each('admin.organization.note', $worker->business, 'note')
    </ul>
</div>
@endsection
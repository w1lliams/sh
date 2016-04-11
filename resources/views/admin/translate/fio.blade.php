@extends('layouts.admin')
@section('content')
<div class="container page-fio-translate">
    <div class="row">
        <div class="col-xs-4">
            <input id="f" type="text" class="form-control" placeholder="Фамилия">
        </div>
        <div class="col-xs-4">
            <input id="i" type="text" class="form-control" placeholder="Имя">
        </div>
        <div class="col-xs-4">
            <input id="o" type="text" class="form-control" placeholder="Отчество">
        </div>
    </div>

    <div class="row">
        <button class="btn btn-primary col-xs-12" ><i class="ion-arrow-right-c"></i></button>
    </div>

    <div class="row">
        <div class="col-xs-4">
            <input type="text" class="form-control" placeholder="Фамилия UA">
        </div>
        <div class="col-xs-4">
            <input type="text" class="form-control" placeholder="Имя UA">
        </div>
        <div class="col-xs-4">
            <input type="text" class="form-control" placeholder="Отчество UA">
        </div>
    </div>
</div>

@endsection
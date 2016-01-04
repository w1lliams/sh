@extends('layouts.admin')

@section('content')
    <div class="container">
        <!-- Display Validation Errors -->
        @include('common.errors')

        <form action="/admin/organization/create" class="form-horizontal">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="fInputStatus" class="col-md-2 control-label">Статус организации</label>
                <div class="col-md-10"><input type="text" class="form-control" id="fInputStatus"
                                              placeholder="Статус организации"></div>
            </div>
            <div class="form-group">
                <label for="fInputCode" class="col-md-2 control-label">ЄДРПОУ код</label>
                <div class="col-md-10"><input type="number" class="form-control" id="fInputCode"
                                              placeholder="ЄДРПОУ код"></div>
            </div>
            <div class="form-group">
                <label for="fInputOPF" class="col-md-2 control-label"><abbr
                            title="организационно-правовая форма">ОПФ</abbr></label>
                <div class="col-md-10"><input type="text" class="form-control" id="fInputOPF" placeholder="ОПФ"></div>
            </div>
            <div class="form-group">
                <label for="fInputName" class="col-md-2 control-label">Название полное</label>
                <div class="col-md-10"><input type="text" class="form-control" id="fInputName"
                                              placeholder="Название полное"></div>
            </div>
            <div class="form-group">
                <label for="fInputNameShort" class="col-md-2 control-label">Название сокращенное</label>
                <div class="col-md-10"><input type="text" class="form-control" id="fInputNameShort"
                                              placeholder="Название сокращенное"></div>
            </div>
            <div class="form-group">
                <label for="fInputAddress" class="col-md-2 control-label">Адрес</label>
                <div class="col-md-10"><input type="text" class="form-control" id="fInputAddress" placeholder="Адрес">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Телефон</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Телефон">
                        <span class="input-group-btn"><button class="btn btn-default" type="button">+</button></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">E-Mail</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="E-Mail">
                        <span class="input-group-btn"><button class="btn btn-default" type="button">+</button></span>
                    </div>
                </div>
            </div>

            <div class="col-md-offset-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
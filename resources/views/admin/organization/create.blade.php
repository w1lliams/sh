@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4>Создание организации</h4>
        <br>
        @include('common.errors')
        <form action="{{url('admin/organization/create')}}" method="post" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="fInputStatus" class="col-md-2 control-label">Статус организации</label>
                <div class="col-md-10">
                    <select class="multiselect" name="status">
                        @foreach($statuses as $status)
                            <option value="{{$status->id}}" @if(old('status') == $status->id) selected="selected" @endif>{{$status->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="fInputOPF" class="col-md-2 control-label">
                    <abbr title="организационно-правовая форма">ОПФ</abbr>
                </label>
                <div class="col-md-10">
                    <select class="multiselect" name="opf">
                        @foreach($opfs as $opf)
                            <option value="{{$opf->id}}" @if(old('opf') == $opf->id) selected="selected" @endif>{{$opf->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="fInputType" class="col-md-2 control-label">Тип</label>
                <div class="col-md-10">
                    <select class="multiselect" name="type">
                        @foreach($types as $type)
                            <option value="{{$type->id}}" @if(old('type') == $type->id) selected="selected" @endif>{{$type->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="fInputCode" class="col-md-2 control-label">ЄДРПОУ код</label>
                <div class="col-md-10">
                    <input type="number" value="{{old('edrpou')}}" name="edrpou" class="form-control" id="fInputCode" placeholder="ЄДРПОУ код">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputName" class="col-md-2 control-label">Название полное</label>
                <div class="col-md-10">
                    <input type="text" value="{{old('fullName')}}" name="fullName" class="form-control" id="fInputName" placeholder="Название полное">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputNameShort" class="col-md-2 control-label">Название сокращенное</label>
                <div class="col-md-10">
                    <input type="text" value="{{old('shortName')}}" name="shortName" class="form-control" id="fInputNameShort" placeholder="Название сокращенное">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputPostCode" class="col-md-2 control-label">Почтовый индекс</label>
                <div class="col-md-10">
                    <input type="number" class="form-control" name="postCode"  value="{{old('postCode')}}" id="fInputPostCode" placeholder="Почтовый индекс">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputCity" class="col-md-2 control-label">Город</label>
                <div class="col-md-10">
                    <select class="multiselect" name="city">
                        <option value="">Выберите город</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}" @if(old('city') == $city->id) selected="selected" @endif>{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="fInputAddress" class="col-md-2 control-label">Адрес</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{old('address')}}" name="address" id="fInputAddress" placeholder="Адрес">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">Телефон</label>
                <div class="col-md-10">
                    <input type="text" name="phone[]" class="form-control" placeholder="Телефон">
                    <a class="pull-right duplicateForm">добавить еще телефон</a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">E-Mail</label>
                <div class="col-md-10">
                    <input type="email" name="email[]" class="form-control" placeholder="E-Mail">
                    <a class="pull-right duplicateForm">добавить еще e-mail</a>
                </div>
            </div>

            <div class="col-md-offset-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
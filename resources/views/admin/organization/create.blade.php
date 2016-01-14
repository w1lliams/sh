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
                <label for="fInputCode" class="col-md-2 control-label">ЄДРПОУ код</label>
                <div class="col-md-10">
                    <input type="number" value="{{old('edrpou')}}" name="edrpou" class="form-control" id="fInputCode" placeholder="ЄДРПОУ код">
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
                <label for="fInputName" class="col-md-2 control-label">Название полное</label>
                <div class="col-md-10">
                    <input type="text" value="{{old('fullName')}}" name="fullName" class="form-control" id="fInputName" placeholder="Название полное">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputNameShort" class="col-md-2 control-label">Название сокращенное</label>
                <div class="col-md-10">
                    <input type="text" value="{{old('name')}}" name="name" class="form-control" id="fInputNameShort" placeholder="Название сокращенное">
                </div>
            </div>
            <div class="form-group">
                <label for="fInputAddress" class="col-md-2 control-label">Адрес</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" id="fInputAddress" placeholder="Адрес">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">Телефон</label>
                <div class="col-md-10">
                    <input type="text" name="phone[]" class="form-control" placeholder="Телефон" id="phoneblk">
                    <a class="pull-right" href="javascript:formHelper.duplicate('#phoneblk', {prepend: true})">добавить еще телефон</a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">E-Mail</label>
                <div class="col-md-10">
                    <input type="email" name="email[]" class="form-control" placeholder="E-Mail" id="emailblk">
                    <a class="pull-right" href="javascript:formHelper.duplicate('#emailblk', {prepend: true})">добавить еще e-mail</a>
                </div>
            </div>

            <div class="col-md-offset-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
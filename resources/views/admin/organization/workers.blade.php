@extends('layouts.admin')

@section('content')
<div class="container page-workers">
    <h4>Работники</h4>
    <form action="" method="post">
        {{csrf_field()}}
        <div class="input-group input-group-lg">
            <input type="file" class="form-control" name="file">
            <span class="input-group-btn"><button class="btn btn-primary" type="submit">Загрузить</button></span>
        </div>
    </form>

    <div class="file"></div>
</div>
@endsection
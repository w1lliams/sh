@extends('layouts.admin')

@section('content')
<div class="container page-workers">
    @include('admin.organization.menu')
    <input type="file" class="form-control input-lg" name="file">

    <div class="btn btn-block btn-primary done">Все верно, сохранить работников</div>

    <div class="organization"></div>
    <div class="file"></div>
</div>
@endsection
@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4>Города</h4>
        @include('common.errors')
        <form action="{{url('admin/city/create')}}" method="post">
            {{csrf_field()}}
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" placeholder="Название города" name="name">
                <span class="input-group-btn"><button class="btn btn-primary" type="submit">Создать</button></span>
            </div>
        </form>
        <br>
        <br>
        <br>
        <ul class="list-group">
            @forelse ($cities as $city)
                <li class="list-group-item">
                    <form action="{{url('admin/city/'. $city->id)}}" method="post" class="pull-right">
                        {{csrf_field()}}
                        {{method_field('DELETE')}}
                        <button type="submit" class="btn btn-link btn-sm mt-5"><i class="ion-ios-close-outline fs20"></i></button>
                    </form>
                    {{$city->name}}
                </li>
            @empty
                <p class="alert alert-warning">Еще нет ни одной записи</p>
            @endforelse
        </ul>
    </div>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container">
    <h4>
        <a class="pull-right" href="{{url('admin/organization/create')}}"><i class="ion-ios-plus-outline"></i></a>
        Организации
    </h4>
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
                    <td>{{$organization->fullName}}</td>
                    <td>{{$organization->city->name or '' }}</td>
                    <td>{{$organization->status->name}}</td>
                    <td>
                        <form action="{{url("admin/organization/{$organization->id}/remove")}}" method="post">
                            {{csrf_field()}}
                            {{method_field('DELETE')}}
                            <button type="submit" class="fs20 btn-invisible">
                                <i class="ion-ios-trash-outline"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $organizations->links() !!}
</div>
@endsection
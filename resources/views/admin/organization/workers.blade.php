@extends('layouts.admin')
@section('content')
<div class="container page-workers">
    <h4>Список работников от &laquo;{{$snapshot->created_at}}&raquo;</h4>
    <ul class="workers-list">
        @foreach($workers as $departmentName => $department)
            <li class="department">
                @if(!empty($department['workers']))
                    <span>{{$departmentName}}</span>
                    <ul>
                    @foreach($department['workers'] as $worker)
                        <li>{{$worker->fio}}, {{$worker->position}}</li>
                    @endforeach
                    </ul>
                @endif

                @if(!empty($department['sub']))
                    @foreach($department['sub'] as $subDepartmentName => $subWorkers)
                        <ul>
                            <li class="department">
                                <span>{{$subDepartmentName}}</span>
                                <ul>
                                @foreach($subWorkers as $worker)
                                    <li>{{$worker->fio}}, {{$worker->position}}</li>
                                @endforeach
                                </ul>
                            </li>
                        </ul>
                    @endforeach
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endsection
@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4>Отзывы</h4>

        <div class="list-group">
            @forelse($feedbacks as $feedback)
                <div class="list-group-item">
                    <div class="list-group-item-heading">
                        {{$feedback->created_at->format('d.m.Y H:i')}}
                        @if(!empty($feedback->worker_id))
                            к работнику {{$feedback->worker->fio}} (
                            <a target="_blank" href="{{route('admin::worker_notes', ['worker' => $feedback->worker->id])}}">adm</a> /
                            <a target="_blank" href="{{route('worker', ['worker' => $feedback->worker->id])}}">site</a>
                            )
                        @endif
                    </div>
                    <p >{{$feedback->text}}</p>

                    <div class="actions clearfix">
                        <a onclick="return confirm('удалить сообщение?');" href="{{route('admin::delete_feedback', ['feedback' => $feedback->id])}}" class="pull-right">Удалить</a>
                        @if(!empty($feedback->file))
                            <a href="{{url('/upload/'. $feedback->file)}}" target="_blank">Скачать прикрепленный файл</a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="alert alert-warning">Еще нет сообщений</p>
            @endforelse
        </div>
    </div>
@endsection
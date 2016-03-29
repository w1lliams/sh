@extends('layouts.admin')

@section('content')
<div class="container page-snapshots">
    <h4>Снимки списков работников</h4>
    <div class="list-group">
        @foreach($snapshots as $snapshot)
            <a href="{{url("/admin/organization/{$organization->id}/snapshot/{$snapshot->id}")}}" class="list-group-item">
                {{$snapshot->created_at}}
            </a>
        @endforeach
    </div>
</div>
@endsection
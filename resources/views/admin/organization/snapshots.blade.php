@extends('layouts.admin')

@section('content')
<div class="container page-snapshots">
    @include('admin.organization.menu')
    <div class="list-group">
        @foreach($snapshots as $snapshot)
            <a href="{{url("/admin/organization/{$organization->id}/snapshot/{$snapshot->id}")}}" class="list-group-item">
                {{$snapshot->date ? $snapshot->date->format('d.m.Y') : ''}} - загружен {{$snapshot->created_at}}
            </a>
        @endforeach
    </div>
</div>
@endsection

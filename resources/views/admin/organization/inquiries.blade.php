@extends('layouts.admin')

@section('content')
<div class="container page-snapshots">
    @include('admin.organization.menu')
    <form class="form row" action="{{route('admin::add_organization_inquiry', $organization->id)}}" method="post">
      {{csrf_field()}}
      <div class="form-group col-sm-5">
        <select class="form-control" name="inquiry">
          @foreach($inquiries as $inquiry)
            <option value="{{$inquiry->id}}">{{$inquiry->name}}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-sm-5">
        <input type="text" name="note" class="form-control" placeholder="примечание">
      </div>
      <button type="submit" class="btn btn-primary col-sm-2">Добавить</button>
    </form>

    <br>
    <br>
    <br>
    <div class="list-group">
        @forelse($organizationInquiries as $organizationInquiry)
          <div class="list-group-item inquiry-item">
            <form action="{{route('admin::remove_inquiry', ['organization' => $organization->id, 'inquiry' => $organizationInquiry->id])}}" method="post" class="pull-right">
                {{csrf_field()}}
                {{method_field('DELETE')}}
                <button type="submit" class="btn btn-link btn-sm mt-5"><i class="ion-ios-close-outline fs20"></i></button>
            </form>

            <span class="inquiry" data-id="{{$organizationInquiry->id}}" data-note="{{$organizationInquiry->note}}">
              @if($organizationInquiry->inquiry)
                  {{$organizationInquiry->inquiry->name}} <span class="text-muted">({{$organizationInquiry->created_at}})</span>
                  <br>
                  {{$organizationInquiry->note}}
              @else
                  {{$organizationInquiry->inquiry_id}}
              @endif
            </span>
          </div>
        @empty
            <p class="alert alert-warning">Еще нет ни одной записи</p>
        @endforelse
    </div>
</div>
@endsection

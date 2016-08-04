@extends('layouts.app')

@section('content')
<div class="help-page">
  <div class="g-search-block">
    <div class="container">
      @include('site.parts.search')
    </div>
  </div>

  <div class="container feedback-page">
    <div class="row">
      <div class="col-md-9 content-block">
        <div class="info-block">
          <div class="icon">
            {{--<i class="sprite tile search"></i>--}}
          </div>
          <div class="info">
            <h1>Повидомлення</h1>

            @include('common.errors')

            @if(session('done'))
              <div class="alert-success">
                Ваш отзыв успешно добавлен
              </div>
            @endif

            <form action="{{route('save_feedback')}}" enctype="multipart/form-data" method="post">
              <input type="hidden" name="worker" value="{{$workerId}}">
              {{csrf_field()}}

              <div class="form-group">
                <label for="input-text1">Сообщение</label>
                <textarea name="text" id="input-text1" class="form-control"></textarea>
              </div>
              <div class="form-group">
                <label for="exampleInputFile">Добавьте файл</label>
                <input name="file" type="file" id="exampleInputFile">
              </div>
              <button type="submit" class="btn btn-success">Надислаты</button>
            </form>
          </div>
        </div>
      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

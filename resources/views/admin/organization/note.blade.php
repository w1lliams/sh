<li class="list-group-item">
    <form method="post" onsubmit="return confirm('Удаляем?')" action="{{route('admin::delete_note', $note->id)}}" class="pull-right">
        {{method_field('delete')}}
        {{csrf_field()}}
        <button type="submit" class="btn btn-default btn-xs">удалить</button>
    </form>
    {{$note->text}}
    <br>
    <a href="{{$note->url}}">{{$note->url}}</a>
</li>
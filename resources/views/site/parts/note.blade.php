<div class="note">
    {{$note->text}}
    @if(!empty($note->url))
        <a href="{{$note->url}}" target="_blank">Читати</a>
    @endif
</div>
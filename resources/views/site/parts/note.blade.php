<div class="note">
    {{$note->text}}<? if (!in_array(substr($note->text,-1), array(".","!","?",";"))) echo '.';?>
    @if(!empty($note->url))
        <a href="{{$note->url}}" target="_blank">Читати</a>
    @endif
</div>
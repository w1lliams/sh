@if (isset($errors) && count($errors) > 0)
<div class="alert alert-danger">
    <strong>Произошла ошибка!</strong>
    <br><br>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
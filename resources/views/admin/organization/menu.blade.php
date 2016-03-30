<ul class="nav nav-pills">
    <li role="presentation" @if($menu == 'edit') class="active" @endif><a href="{{route('admin::edit_organization', $organization->id)}}">Редактирование</a></li>
    <li role="presentation" @if($menu == 'workers') class="active" @endif><a href="{{route('admin::workers', $organization->id)}}">Сотрудники</a></li>
    <li role="presentation" @if($menu == 'add_workers') class="active" @endif><a href="{{route('admin::add_workers', $organization->id)}}">Загрузить сотрудников</a></li>
</ul>
<br>
<br>
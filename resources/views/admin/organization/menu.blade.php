<ul class="nav nav-pills">
    <li role="presentation" @if($menu == 'edit') class="active" @endif><a href="{{route('admin::edit_organization', $organization->id)}}">Редактирование</a></li>
    <li role="presentation" @if($menu == 'workers') class="active" @endif><a href="{{route('admin::workers', $organization->id)}}">Сотрудники</a></li>
    <li role="presentation" @if($menu == 'add_workers') class="active" @endif><a href="{{route('admin::add_workers', $organization->id)}}">Загрузить сотрудников</a></li>
    @if(isset($organization) && empty($organization->parent_id))
        <li role="presentation" @if($menu == 'add_department') class="active" @endif><a href="{{route('admin::add_department', $organization->id)}}">Добавить подразделение</a></li>
    @endif
    <li role="presentation" @if($menu == 'inquiries') class="active" @endif><a href="{{route('admin::organization_inquiries', $organization->id)}}">Взаимодействия (Запросы)</a></li>
</ul>
<br>
<br>

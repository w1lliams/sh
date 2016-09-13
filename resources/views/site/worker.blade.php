@extends('layouts.app')

@section('head')
    <title>{{$worker->fio}} - відгуки, оцінка роботи. {{$worker->organization->fullName}}@if(!empty($worker->organization->city->name)) (м. {{$worker->organization->city->name}})@endif</title>
@endsection

@section('content')
<div class="worker-page">
  <div class="g-search-block">
    <div class="container">
      @include('site.parts.search')
    </div>
  </div>

<?
//
	// Смотрим, есть ли для этого работника файлы недвижимости, авто или предпринимательства. Они будут браться не из базы, а напрямую из папок
	$WorkerFilesDir = '/data/'.base32encode($worker->fio);
	$worker->notes_count +=get_additional_notes_count($worker->fio);
	$WorkerFiles = get_additional_notes_count($worker->fio, true);	// получаем массив со списком файлов недвижимости, авто, ...

?>


  <div class="container">
    <div class="row">
      <div class="col-md-9 content-block">
        <div class="info-block">
          <div class="icon">
            <i class="sprite tile worker"></i>
          </div>
          <div class="info">
            <h1>{{$worker->fio}}</h1>
            <div class="position  text-muted">
              {{$worker->position}}@if(!empty($worker->department));
                {{$worker->department}}
              @endif
              @if(!empty($worker->subDepartment))
                {{$worker->subDepartment}},
              @endif
            </div>

            <div class="organization  text-muted">
              {{$worker->organization->fullName}}@if(!empty($worker->organization->city->name)) (м. {{$worker->organization->city->name}})@endif, <a href="{{route('organization', $worker->organization->id)}}">всі&nbsp;працівники</a>
            </div>
          </div>
        </div>


        <div class="info-block">
           <div class="icon">
               <i class="sprite tile otherpositions"></i>
           </div>
           <div class="info">
               <h5>Однофамільці чи можливі інші або попередні місця роботи</h5>
		Зверніть увагу, інформація в цьому пункті знайдена автоматично за ПІБ серед всіх працівників наявних організацій, а тому в переліку можуть бути інші особи з ідентичними ПІБ (однофамільці).
           </div>
        </div>


        @if($worker->notes_count > 0)
              @if(count($worker->publications) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile news"></i>
                      </div>
                      <div class="info">
                          <h5>Публікації в ЗМІ</h5>
                          <p>@each('site.parts.note', $worker->publications, 'note')</p>
			  <p>Якщо Вам відомі інші факти в ЗМІ про особу, Ви можете <a href="{{route('feedback', ['w' => $worker->id])}}">додати посилання</a>.
			  Приймаються як посилання на сайти ЗМІ, так і повідомлення або відгуки на форумах 
			  від коритувачів із репутацією, особистих сайтах, блогах, сторінках соцмереж публічних
			  осіб або громадських активістів</p>
                      </div>
                  </div>
              @endif

              @if(count($worker->finance) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile money"></i>
                      </div>
                      <div class="info">
                          <h5>Доходи <?=morpher_ukr_inflect($worker->fio, 'rod')?></h5>
                          @each('site.parts.note', $worker->finance, 'note')
                      </div>
                  </div>
              @endif

<!--	     
              @if(count($worker->realty) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile realty"></i>
                      </div>
                      <div class="info">
                          <h5>Результати пошуку в реєстрі нерухомості</h5>
			  <p>За запитом "{{$worker->fio}}" в Державному реєстрі речових прав на нерухоме майно знайдено:</p>
                          <p>@each('site.parts.note', $worker->realty, 'note')</p>
			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif
-->

		@if(count($WorkerFiles['WorkerRealty']) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile realty"></i>
                      </div>
                      <div class="info">
                          <h5>Результати пошуку в реєстрі нерухомості</h5>
			  <p>За запитом "{{$worker->fio}}" в Державному реєстрі речових прав на нерухоме майно знайдено:</p>
			  <p><? foreach ($WorkerFiles['WorkerRealty'] as $file) {echo "- станом на ".date("d.m.Y", strtotime(preg_replace('~[^0-9-]+~','',$file)))." року. <a href='$WorkerFilesDir/$file' target=_blank>Переглянути витяг</a><br>";}?></p>
			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif

<!--
              @if(count($worker->cars) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile cars"></i>
                      </div>
                      <div class="info">
                          <h5>Результати пошуку в реєстрі транспортних засобів</h5>
			  <p>За запитом "{{$worker->fio}}" в Єдиному державному реєстрі МВС знайдено:</p>
                          <p>@each('site.parts.note', $worker->cars, 'note')</p>
  			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif
-->

              @if(count($WorkerFiles['WorkerCars']) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile cars"></i>
                      </div>
                      <div class="info">
                          <h5>Результати пошуку в реєстрі транспортних засобів</h5>
			  <p>За запитом "{{$worker->fio}}" в Єдиному державному реєстрі МВС знайдено:</p>
			  <p><? foreach ($WorkerFiles['WorkerCars'] as $file) {echo "- станом на ".date("d.m.Y", strtotime(preg_replace('~[^0-9-]+~','',$file)))." року. <a href='$WorkerFilesDir/$file' target=_blank>Переглянути результат</a><br>";}?></p>
  			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif

              @if(count($worker->law) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile law"></i>
                      </div>
                      <div class="info">
                          <h5>Справи в судах</h5>
                          @each('site.parts.note', $worker->law, 'note')
                      </div>
                  </div>
              @endif

<!--
              @if(count($worker->business) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile business"></i>
                      </div>
                      <div class="info">
                          <h5>Результат пошуку в реєстрі юридичних осіб та підприємців</h5>
			  <p>За запитом "{{$worker->fio}}" в Єдиному державному реєстрі юридичних осіб, фізичних осіб-підприємців та громадських формувань знайдено:</p>

  			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif
-->

              @if(count($WorkerFiles['WorkerBusiness']) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile business"></i>
                      </div>
                      <div class="info">
                          <h5>Результат пошуку в реєстрі юридичних осіб та підприємців</h5>
			  <p>За запитом "{{$worker->fio}}" в Єдиному державному реєстрі юридичних осіб, фізичних осіб-підприємців та громадських формувань знайдено:</p>
			  <p><? foreach ($WorkerFiles['WorkerBusiness'] as $file) {echo "- станом на ".date("d.m.Y", strtotime(preg_replace('~[^0-9-]+~','',$file)))." року. <a href='$WorkerFilesDir/$file' target=_blank>Переглянути</a><br>";}?></p>
  			  <p>Зверніть увагу, в результатах пошуку можуть бути інші особи з ідентичними ПІБ (однофамільці).</p>
                      </div>
                  </div>
              @endif


                  
        @endif


              @if(count($worker->publications) == 0 || count($worker->finance) == 0 || count($worker->realty) == 0 || count($worker->cars) == 0 || count($worker->law) == 0 || count($worker->business) == 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile search"></i>
                      </div>
                      <div class="info">
                          <h5>Пошук додаткової інформації про <?=morpher_ukr_inflect($worker->fio, 'zna')?></h5>
	                  @if(count($worker->publications) == 0)Якщо Вам відомі будь-які факти про цю особу, Ви можете <a href="{{route('feedback', ['w' => $worker->id])}}">додати посилання</a> на відповідний сайт. 
			  Приймаються як посилання на сайти ЗМІ, так і повідомлення або відгуки на форумах від коритувачів із репутацією, особистих сайтах, блогах, сторінках соцмереж публічних осіб або громадських активістів.<br>
  	                  @endif
			  Для цієї особи також можна спробувати отримати інформацію про
			  @if(count($worker->finance) == 0)доходи,@endif
			  @if(count($worker->realty) == 0 && count($WorkerFiles['WorkerRealty']) == 0)нерухомість,@endif
			  @if(count($worker->cars) == 0 && count($WorkerFiles['WorkerCars']) == 0)автотранспортні засоби,@endif
			  @if(count($worker->law) == 0)відомості про участь у судових справах,@endif
			  @if(count($worker->business) == 0 && count($WorkerFiles['WorkerBusiness']) == 0)інформацію про участь у діяльності підприємств або здійснення підприємницької діяльності,@endif
			  тощо.
			  Як це можна зробити, <a href=/help>читайте тут</a>
                      </div>
                  </div>
              @endif


                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile share"></i>
                      </div>
                      <div class="info">
                          <h5>Поділитися</h5>
			  <div>Не залишайтесь осторонь. Допоможіть нам поширити знайдену інформацію. Розкажіть про цю сторінку іншим:</div>
			<script type="text/javascript">(function() {
			  if (window.pluso)if (typeof window.pluso.start == "function") return;
			  if (window.ifpluso==undefined) { window.ifpluso = 1;
			    var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
			    s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
			    s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
			    var h=d[g]('body')[0];
			    h.appendChild(s);
			  }})();</script>
			<div class="pluso" style="margin-top:4px; height:28px;" data-background="none;" data-options="small,square,line,horizontal,nocounter,sepcounter=1,theme=14" data-services="facebook,vkontakte,odnoklassniki,twitter"></div>
                      </div>
                  </div>



                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile comments"></i>
                      </div>
                      <div class="info">
                          <h5>Відгуки, подяки, скарги</h5>
			  Відгуки про <?=morpher_ukr_inflect($worker->fio, 'zna')?> можна залишати анонімно та без реєстрації. Для абсолютної анонімності встановіть та використовуйте <a href=https://www.torproject.org/projects/torbrowser.html.en target=_blank>браузер Tor</a>. Будьте ввічливими, відверто агресивні повідомлення та такі, що суперечать чинному законодавству можуть бути видалені.


				<div id="hypercomments_widget"></div>
				<script type="text/javascript">
				_hcwp = window._hcwp || [];

				_hcwp.push({widget:"Stream", widget_id: 79747, eager_load:true, title:'<?=$worker->fio.'. '.$worker->organization->fullName?>', css:"/css/comments.css", xid: "<?// задаем одинаковые id для страниц с одним сотрудником, который работает в одной организации на разных должностях или в разных отделах
											if(!empty($worker->organization->parent_id)) echo base32encode($worker->organization->parent->edrpou.' '.$worker->fio); else echo base32encode($worker->organization->edrpou.' '.$worker->fio); 
											?>"});
				(function() {
				if("HC_LOAD_INIT" in window)return;
				HC_LOAD_INIT = true;
				var lang = "uk";
				var hcc = document.createElement("script"); hcc.type = "text/javascript"; hcc.async = true;
				hcc.src = ("https:" == document.location.protocol ? "https" : "http")+"://w.hypercomments.com/widget/hc/79747/"+lang+"/widget.js";
				var s = document.getElementsByTagName("script")[0];
				s.parentNode.insertBefore(hcc, s.nextSibling);
				})();
				</script>

                      </div>
                  </div>

      </div>

      @include('site.parts.donate')
      @include('site.parts.vkandfb')

    </div>
  </div>
</div>


@endsection


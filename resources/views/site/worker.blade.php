@extends('layouts.app')

@section('content')
<div class="worker-page">
  <div class="g-search-block">
    <div class="container">
      @include('site.parts.search')
    </div>
  </div>

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
              {{$worker->organization->fullName}}, <a href="{{route('organization', $worker->organization->id)}}">всі працівники</a>
            </div>
          </div>
        </div>


        <div class="info-block">
           <div class="icon">
               <i class="sprite tile otherpositions"></i>
           </div>
           <div class="info">
               <h5>Можливі інші або попередні місця роботи, однофамільці</h5>
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
                          @each('site.parts.note', $worker->publications, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->finance) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile money"></i>
                      </div>
                      <div class="info">
                          <h5>Доходи</h5>
                          @each('site.parts.note', $worker->finance, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->realty) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile realty"></i>
                      </div>
                      <div class="info">
                          <h5>Нерухомість</h5>
                          @each('site.parts.note', $worker->realty, 'note')
                      </div>
                  </div>
              @endif

              @if(count($worker->cars) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile cars"></i>
                      </div>
                      <div class="info">
                          <h5>Автомобілі</h5>
                          @each('site.parts.note', $worker->cars, 'note')
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

              @if(count($worker->business) > 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile business"></i>
                      </div>
                      <div class="info">
                          <h5>Підприємства та підприємництво</h5>
                          @each('site.parts.note', $worker->business, 'note')
                      </div>
                  </div>
              @endif

                  <a href="{{route('feedback', ['w' => $worker->id])}}">add feedback</a>

        @endif


              @if(count($worker->publications) == 0 || count($worker->finance) == 0 || count($worker->realty) == 0 || count($worker->cars) == 0 || count($worker->law) == 0 || count($worker->business) == 0)
                  <div class="info-block">
                      <div class="icon">
                          <i class="sprite tile search"></i>
                      </div>
                      <div class="info">
                          <h5>Пошук додаткової інформації про особу</h5>
			  Для цієї особи можна спробувати отримати інформацію про
			  @if(count($worker->publications) == 0)публікації в ЗМІ,@endif
			  @if(count($worker->finance) == 0)доходи,@endif
			  @if(count($worker->realty) == 0)нерухомість,@endif
			  @if(count($worker->cars) == 0)автотранспортні засоби,@endif
			  @if(count($worker->law) == 0)відомості про участь у судових справах,@endif
			  @if(count($worker->business) == 0)інформацію про участь у діяльності підприємств або здійснення підприємницької діяльності,@endif
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
			  Відгуки можна залишати анонімно та без реєстрації. Для абсолютної анонімності встановіть та використовуйте <a href=https://www.torproject.org/projects/torbrowser.html.en target=_blank>браузер Tor</a>. Будьте ввічливими, відверто агресивні повідомлення та такі, що суперечать чинному законодавству можуть бути видалені.


				<div id="hypercomments_widget"></div>
				<script type="text/javascript">
				_hcwp = window._hcwp || [];
				_hcwp.push({widget:"Stream", widget_id: 79729, xid: "MDExMjc3OTlf0JPQvtC70ZbQutC+0LIg0JLQvtC70L7QtNC40LzQuNGAINCS0L7Qu9C+0LTQuNC80LjRgNC+0LLQuNGH"});
				(function() {
				if("HC_LOAD_INIT" in window)return;
				HC_LOAD_INIT = true;
				var lang = "uk";
				var hcc = document.createElement("script"); hcc.type = "text/javascript"; hcc.async = true;
				hcc.src = ("https:" == document.location.protocol ? "https" : "http")+"://w.hypercomments.com/widget/hc/79729/"+lang+"/widget.js";
				var s = document.getElementsByTagName("script")[0];
				s.parentNode.insertBefore(hcc, s.nextSibling);
				})();
				</script>


                      </div>
                  </div>

      </div>

      @include('site.parts.donate')
    </div>
  </div>
</div>
@endsection

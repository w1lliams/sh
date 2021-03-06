@extends('layouts.app')

@section('head')
  <title>Як знайти компромат про чиновника? Долучайся до громадського руху "СтопХаб" та допомагай у пошуку суспільно-необхідної інформації</title>
@endsection

@section('content')
<div class="help-page">
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
            <i class="sprite tile search"></i>
          </div>
          <div class="info">
            <h1>Допомагай нам шукати інформацію</h1>
            <p>
              Відносно кожного чиновника або працівника можна спробувати отримати
              інформацію про публікації у ЗМІ, доходи, нерухомість, автотранспортні засоби,
              відомості про участь у судових справах, інформацію про участь у діяльності
              підприємств, або здійснення підприємницької діяльності.
            </p>
            <p>
              Повідомте нам все, що знайшли і найлижчим часом інформація з’явиться на
              сайті СтопХаб.
            </p>
            <a href="{{route('feedback')}}" class="btn btn-success">Повідомити</a>

            <h3>Як залишити відгук про чиновника?</h3>
            <p>
	      Відгук про роботу чиновника, скаргу чи подяку можна залишити на сторінці відповідної посадової особи. Відгуки можна залишати анонімно, вказавши лише своє ім'я чи псевдонім. Для абсолютної
	      анонімності встановіть та використовуйте <a href=https://www.torproject.org/projects/torbrowser.html.en target=_blank>браузер Tor</a>. Будьте ввічливими, відверто агресивні повідомлення та такі, що суперечать чинному законодавству можуть бути видалені.
            </p>


            <h3>Як знайти інформацію про публікації в ЗМІ?</h3>
            <p>
              Скористайтеся пошуком <a href=http://google.com.ua target=_blank>Google</a>, зазначивши в пошуковому запиті прізвище, ім’я
              чиновника та його місце роботи. Запити доцільно робити на двох мовах: українській та
              російській. За наявності великої кількості результатів, рекомендуємо додати до запиту
              ключові слова, що можуть пов’язувати особу із порушенням Закону. <br>
              Приклади пошукових запитів: <br>
              «Боделан Владимир МЧС выговор» <br>
              «Буран Олексій Малиновський суд хабар» <br>
              «Батурин Виталий Николаевич КП Городские дороги нарушение»
            </p>
            <p>
              Посилання на знайдені статті Ви можете <a href=/feedback>відправити нам</a>. Це також можна зробити на
              сторінці відповідного працівника, натиснувши на «додати посилання» у інформаційному блоці
              «Публікації в ЗМІ»
            </p>

            <h3>Як знайти інформацію про доходи?</h3>
            <p>
              Знайти таку інформацію можна також за допомогою пошукових систем.
              Відповідно до законодавства деякі чиновники щорічно заповнюють обов’язкові декларації
              про доходи. Якщо Вам відома сторінка в мережі Інтернет, де опубліковано таку декларацію,
              <a href=/feedback>повідомте нам</a> про це.
            </p>

            <h3>Як знайти інформацію про нерухомість?</h3>
            <p>
              Інформацію про нерухомість можна отримати в електронній формі із Державного реєстру речових прав
              на нерухоме майно. Вартість отримання такого витягу без урахування комісії складає 17 грн. Витяг формується
              автоматично одразу після сплати онлайн. <br>
              Зверніть увагу, для отримання витягу Вам потрібно або <a href=https://e-court.mail.gov.ua/?_task=login&_action=plugin.registration_rules target=_blank>зареєструватися на сервісі mail.gov.ua</a>, або мати ЕЦП чи бути
              клієнтом банків, що підримують авторизацію BankID (Ощадбанк, Приватбанк та ін.). <br>
              Після реєстрації <a href=https://e-court.mail.gov.ua/ target=_blank>перейдіть на сторінку авторизації</a> та увійшовши до особистого кабінету оберіть «Пошук відомостей                 
              за ідентифікаційними даними фізичної особи». Пошук можливий за прізвищем, ім’ям, по-батькові або за ідентифікаційним
              кодом чи паспортними даними.
            </p>
            <p>Отримавши витяг, <a href=/feedback>надішліть на Стопхаб посилання на нього, або сам файл</a> і незабаром інформацію буде перевірено й опубліковано.</p>


            <h3>Як знайти інформацію про автотранспортні засоби?</h3>
            <p>
              Витяг з Єдиного державного реєстру МВС про володіння фізичною особою транспортними засобами
              надається безкоштовно <a href=https://rq.hsc.gov.ua/ target=_blank>на сайті Головного сервісного центру МВС України</a>. Пройдіть авторизацію, створіть новий запит та через декілька хвилин
              у своєму особистому кабінеті Ви побачите результати пошкуку. Повідомте цю інформацію нам. Якщо вона становить суспільний інтерес та є суспільно-необхідною, - буде незабаром опублікована на сайті Стопхаб.
            </p>

            <h3>Як знайти інформацію про справи в судах?</h3>
            <p>
              Скористайтеся пошуком за ПІБ на офіційному сайті судової влади України у розділі <a href=http://court.gov.ua/fair/ target=_blank>Інформація щодо стадій розгляду
              судових справ</a>. Про результати пошуку можете <a href=/feedback>повідомити нам</a>.
            </p>

            <h3>Як знайти інформацію про підприємства та підприємництво?</h3>
            <p>
              Пошук за ПІБ фізичної особи-підприємця або назвою юридичної в Єдиному державному реєстрі юридичних осіб,
              фізичних осіб-підприємців та громадських формувань надається безкоштовно. <a href=https://usr.minjust.gov.ua/ua/freesearch target=_blank>Перейдіть на сторінку пошуку</a> та,
              отримавши відомості про участь особи в діяльності підприємства (як керівника, засновника, або кінцевого
              бенефіціарного власника) або інформацію про зайняття посадовою особою підприємницькою діяльністю як ФОП,
              <a href=/feedback>повідомте нам</a> про це.
            </p>
            <p>
              В <a href=https://usr.minjust.gov.ua/ua/paidextract target=_blank>платній версії</a> пошук можливий за ПІБ керівника або засновника юридичної особи із наданням
              відповідного витягу. Файл із витягом Ви також можете <a href=/feedback>надіслати на СтопХаб</a>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

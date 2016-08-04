@extends('layouts.app')

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

            <h3>Як знайти інформацію про публікації в ЗМІ?</h3>
            <p>
              Скористайтеся пошуком Google, зазначивши в пошуковому запиті прізвище, ім’я
              чиновника та його місце роботи. Запити доцільно робити на двох мовах: українській та
              російській. За наявності великої кількості результатів, рекомендуємо додати до запиту
              ключові слова, що можуть пов’язувати особу із порушенням Закону. <br>
              Приклади пошукових запитів: <br>
              «Боделан Владимир МЧС выговор» <br>
              «Буран Олексій Малиновський суд хабар» <br>
              «Батурин Виталий Николаевич КП Городские дороги нарушение»
            </p>
            <p>
              Посилання на знайдені статті Ви можете відправити нам. Це також можна зробити на
              сторінці відповідного працівника, натиснувши на «додати посилання» у інформаційному блоці
              «Публікації в ЗМІ»
            </p>

            <h3>Як знайти інформацію про доходи?</h3>
            <p>
              Знайти таку інформацію можна також за допомогою пошукових систем.
              Відповідно до законодавства деякі чиновники щорічно заповнюють обов’язкові декларації
              про доходи. Якщо Вам відома сторінка в мережі Інтернет, де опубліковано таку декларацію,
              повідомте нам про це.
            </p>

            <h3>Як знайти інформацію про нерухомість?</h3>
            <p>
              Інформацію про нерухомість можна отримати в електронній формі із Державного реєстру речових прав
              на нерухоме майно. Вартість отримання такого витягу без урахування комісії складає 17 грн. Витяг формується
              автоматично одразу після сплати онлайн. <br>
              Зверніть увагу, для отримання витягу Вам потрібно або зареєструватися на сервісі mail.gov.ua, або мати ЕЦП чи бути
              клієнтом банків, що підримують авторизацію BankID (Ощадбанк, Приватбанк та ін.). <br>
              Після реєстрації перейдіть на сторінку авторизації та увійшовши до особистого кабінету оберіть «Пошук відомостей
              за ідентифікаційними даними фізичної особи». Пошук можливий за прізвищем, ім’ям, по-батькові або за ідентифікаційним
              кодом чи паспортними даними.
            </p>
            <p>Отримавши витяг, завантажте його на СтопХаб і незабаром інформацію буде опубліковано.</p>


            <h3>Як знайти інформацію про автотранспортні засоби?</h3>
            <p>
              Витяг з Єдиного державного реєстру МВС про володіння фізичною особою транспортними засобами
              надається безкоштовно на порталі державних послуг Igov.org.ua. Пройдіть авторизацію та заповніть форму пошуку,
              зазначивши свою електронну поштову адресу. На цей email Ви отримаєте відповідь, відомості з якої можна
              опублікувати на сайті Стопхаб, повідомивши нам про це.
            </p>

            <h3>Як знайти інформацію про справи в судах?</h3>
            <p>
              Скористайтеся пошуком за ПІБ на офіційному сайті судової влади України у розділі Інформація щодо стадій розгляду
              судових справ. Про результати пошуку можете повідомити нам.
            </p>

            <h3>Як знайти інформацію про підприємства та підприємництво?</h3>
            <p>
              Пошук за ПІБ фізичної особи-підприємця або назвою юридичної в Єдиному державному реєстрі юридичних осіб,
              фізичних осіб-підприємців та громадських формувань надається безкоштовно. Перейдіть на сторінку пошуку та,
              отримавши відомості про участь особи в діяльності підприємства (як керівника, засновника, або кінцевого
              бенефіціарного власника) або інформацію про зайняття посадовою особою підприємницькою діяльністю як ФОП,
              повідомте нам про це.
            </p>
            <p>
              В платній версії вартістю 50 грн. пошук можливий за ПІБ керівника або засновника юридичної особи із наданням
              відповідного витягу. Файл із витягом Ви також можете завантажити на СтопХаб.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

export let organizationWorkers = {
  CHECK_SYMBOLS: /[^АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя№ "'(),-=.\/0123456789;:I]/,
  CHECK_LETTERS: /[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]{2,}/,

  /**
   * Страница импорта файла работников
   */
  index: function () {
    $('input[name=file]').on('change', this._onSelectFile.bind(this));
  },

  /**
   * Пользователь выбрал файл
   * @param e
   * @private
   */
  _onSelectFile: function (e) {
    const file = e.target.files[0];
    if(!file) return;

    const fileReader = new FileReader;
    fileReader.onload = this._checkFile.bind(this);
    fileReader.readAsText(file, 'cp1251');
  },

  /**
   * И тут начинается пиздорез с проверкой
   * @param e
   * @private
   */
  _checkFile: function (e) {
    const lines = e.target.result.split("\n");
    let errors = [],
      warnings = [];

    errors = errors.concat(this._checkFirstLine(lines[0]));

    // получаем сотрудников
    let workers = {main: []},
      currentCategory = null,
      matches;

    for(let i = 1; i < lines.length; i++) {
      const line = lines[i].trim();
      // проверяем на неразрещенные символы
      matches = this.CHECK_SYMBOLS.exec(line);
      if(matches) {
        errors.push(`Неразрещенный символ. line: ${i+1}, symbol: ${matches.index}`);
      }

      // =категория
      matches = /^=([^=]+)$/.exec(line);
      if(matches) {
        if(currentCategory)
          errors.push(`Открытие категории, но предыдущая еще не закрылась. line: ${i+1}`);
        currentCategory = matches[1];
        continue;
      }

      // закрытие категории "=="
      if(/^\s*==\s*$/.test(line)) {
        if(!currentCategory)
          errors.push(`Закрытие категории, но открытия не было. line: ${i+1}`);
        currentCategory = null;
        continue;
      }

      // парсим работников
      const cat = currentCategory ? currentCategory : 'main';
      if(!workers[cat]) workers[cat] = [];

      this._parseWorker(line, {
        workers: workers[cat],
        errors: errors,
        line: i+1
      });
    }

    if (currentCategory)
      errors.push(`Неверно открыты/закрыты категории. "${currentCategory}"`);

    // проверяем работников по БД
    this._checkWorkers(workers, {errors: errors}).then(function(fioErrors) {
      warnings = warnings.concat(fioErrors);
      this._showErrors({
        errors,
        selector:'.errors'
      });
      this._showErrors({
        errors: warnings,
        selector: '.warnings',
        className: 'alert-warning'
      });
    }.bind(this));
  },

  /**
   *
   * @param value
   * @returns {Array}
   * @private
   */
  _checkFirstLine: function (value) {
    let data = value.split(','),
      date = data[0].trim(),
      workersCount = data[1].trim(),
      errors = [];

    // проверяем дату
    if(!/^\d{2}\.\d{2}\.\d{4}$/.test(date))
      errors.push(`Неверный формат даты "${date}" (должен быть <11.11.2222>)`);

    // кол-во сотрудников
    if(!/^(\d+|-)$/.test(workersCount))
      errors.push(`Кол-во сотрудников указано неверно "${workersCount}" (должно быть число либо <->)`);

    return errors;
  },

  /**
   *
   * @param value
   * @param opt
   * @private
   */
  _parseWorker: function (value, opt) {
    let matches = /([^,]+),(.*)$/.exec(value);
    if(!matches) {
      opt.errors.push(`Невозможно распознать ФИО и должность. line: ${opt.line}`);
      return;
    }

    const position = matches[2].trim();
    if (!this.CHECK_LETTERS.test(position)) {
      opt.errors.push(`Невозможно распознать ФИО и должность. line: ${opt.line}`);
      return;
    }

    opt.workers.push({fio: matches[1].trim(), position: position});
  },

  _checkWorkers: function (workers) {
    // отправляем на сервер только массив ФИО
    var data = [];
    for(let i in workers)
      for(let worker of workers[i])
        data.push(worker.fio);

    return $.ajax({
      url: '/admin/workers/check_new_workers',
      method: 'post',
      data: {
        workers: JSON.stringify(data),
        _token: window._token
      }
    });
  },

  /**
   * Выводим ошибки проверки файла
   * @param {{errors: <Array>, selector: <String>, className: <String>}} opt
 * @private
   */
  _showErrors: function (opt) {
    opt = _.extend({className: 'alert-danger'}, opt);
    let html = '';
    if (opt.errors && opt.errors.length > 0)
      html = `<div class="alert ${opt.className}">${opt.errors.join('<br>')}</div>`;
    $(opt.selector).html(html);
  }
};
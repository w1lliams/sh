(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _router = require('./router');

$('.multiselect').multiselect({
  nonSelectedText: 'Выберите значение'
});

},{"./router":4}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var organizationCreate = exports.organizationCreate = {
  /**
   * Страница создания организации
   */
  index: function index() {
    $('.duplicateForm').click(this._cloneFormField);
    $('input[name=address]').on('change', this._onChangeAddress);
  },

  /**
   * При изменении адреса парсим почтовый индекс и город
   * @param e
   * @private
   */
  _onChangeAddress: function _onChangeAddress(e) {
    var $address = $(e.currentTarget);
    //  если есть почтовый индекс в адресе вставляем его в соответствующее поле
    var matches = /^(\d+)/.exec($address.val());
    if (matches) $('input[name=postCode]').val(matches[1]);

    // ищем город и выбираем его в выпадающем списке
    matches = /(?:місто|м\.)\s+([а-яїґ]+)/i.exec($address.val());
    if (matches) {
      var $select = $('select[name=city]');
      var $option = $select.find('option').filter(function (key, option) {
        return $(option).text().toLowerCase() == matches[1].toLowerCase();
      });
      $option.attr('selected', 'true');
      $select.multiselect('refresh');
    }
  },

  /**
   * Дублирование поля ввода
   * @param e
   * @private
   */
  _cloneFormField: function _cloneFormField(e) {
    var $parent = $(e.target.parentNode);
    $parent.prepend($parent.find('input:first').clone().val(''));
  }
};

},{}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var organizationWorkers = exports.organizationWorkers = {
  CHECK_SYMBOLS: /[^АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя№ "'(),-=.\/0123456789;:I]/,
  CHECK_LETTERS: /[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]{2,}/,

  /**
   * Страница импорта файла работников
   */
  index: function index() {
    $('input[name=file]').on('change', this._onSelectFile.bind(this));
  },

  /**
   * Пользователь выбрал файл
   * @param e
   * @private
   */
  _onSelectFile: function _onSelectFile(e) {
    var file = e.target.files[0];
    if (!file) return;

    var fileReader = new FileReader();
    fileReader.onload = this._checkFile.bind(this);
    fileReader.readAsText(file, 'cp1251');
  },

  /**
   * И тут начинается пиздорез с проверкой
   * @param e
   * @private
   */
  _checkFile: function _checkFile(e) {
    var lines = e.target.result.split("\n");
    var errors = [],
        warnings = [];

    errors = errors.concat(this._checkFirstLine(lines[0]));

    // получаем сотрудников
    var workers = { main: [] },
        currentCategory = null,
        matches = undefined;

    for (var i = 1; i < lines.length; i++) {
      var line = lines[i].trim();
      // проверяем на неразрещенные символы
      matches = this.CHECK_SYMBOLS.exec(line);
      if (matches) {
        errors.push('Неразрещенный символ. line: ' + (i + 1) + ', symbol: ' + matches.index);
      }

      // =категория
      matches = /^=([^=]+)$/.exec(line);
      if (matches) {
        if (currentCategory) errors.push('Открытие категории, но предыдущая еще не закрылась. line: ' + (i + 1));
        currentCategory = matches[1];
        continue;
      }

      // закрытие категории "=="
      if (/^\s*==\s*$/.test(line)) {
        if (!currentCategory) errors.push('Закрытие категории, но открытия не было. line: ' + (i + 1));
        currentCategory = null;
        continue;
      }

      // парсим работников
      var cat = currentCategory ? currentCategory : 'main';
      if (!workers[cat]) workers[cat] = [];

      this._parseWorker(line, {
        workers: workers[cat],
        errors: errors,
        line: i + 1
      });
    }

    if (currentCategory) errors.push('Неверно открыты/закрыты категории. "' + currentCategory + '"');

    // проверяем работников по БД
    this._checkWorkers(workers, { errors: errors }).then(function (fioErrors) {
      warnings = warnings.concat(fioErrors);
      this._showErrors({
        errors: errors,
        selector: '.errors'
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
  _checkFirstLine: function _checkFirstLine(value) {
    var data = value.split(','),
        date = data[0].trim(),
        workersCount = data[1].trim(),
        errors = [];

    // проверяем дату
    if (!/^\d{2}\.\d{2}\.\d{4}$/.test(date)) errors.push('Неверный формат даты "' + date + '" (должен быть <11.11.2222>)');

    // кол-во сотрудников
    if (!/^(\d+|-)$/.test(workersCount)) errors.push('Кол-во сотрудников указано неверно "' + workersCount + '" (должно быть число либо <->)');

    return errors;
  },

  /**
   *
   * @param value
   * @param opt
   * @private
   */
  _parseWorker: function _parseWorker(value, opt) {
    var matches = /([^,]+),(.*)$/.exec(value);
    if (!matches) {
      opt.errors.push('Невозможно распознать ФИО и должность. line: ' + opt.line);
      return;
    }

    var position = matches[2].trim();
    if (!this.CHECK_LETTERS.test(position)) {
      opt.errors.push('Невозможно распознать ФИО и должность. line: ' + opt.line);
      return;
    }

    opt.workers.push({ fio: matches[1].trim(), position: position });
  },

  _checkWorkers: function _checkWorkers(workers) {
    // отправляем на сервер только массив ФИО
    var data = [];
    for (var i in workers) {
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = workers[i][Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var worker = _step.value;

          data.push(worker.fio);
        }
      } catch (err) {
        _didIteratorError = true;
        _iteratorError = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion && _iterator.return) {
            _iterator.return();
          }
        } finally {
          if (_didIteratorError) {
            throw _iteratorError;
          }
        }
      }
    }return $.ajax({
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
  _showErrors: function _showErrors(opt) {
    opt = _.extend({ className: 'alert-danger' }, opt);
    var html = '';
    if (opt.errors && opt.errors.length > 0) html = '<div class="alert ' + opt.className + '">' + opt.errors.join('<br>') + '</div>';
    $(opt.selector).html(html);
  }
};

},{}],4:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.router = undefined;

var _organization_create = require('./controllers/organization_create');

var _organization_workers = require('./controllers/organization_workers');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Router =
/**
 * Прогоняем роутфы и выполняем первый подходящий
 * @param url
 */
function Router() {
  var url = arguments.length <= 0 || arguments[0] === undefined ? window.location.pathname : arguments[0];

  _classCallCheck(this, Router);

  // роуты добавлять здесь
  var rules = [[_organization_create.organizationCreate.index.bind(_organization_create.organizationCreate), /admin\/organization\/(create|\d+\/edit)$/], [_organization_workers.organizationWorkers.index.bind(_organization_workers.organizationWorkers), /admin\/organization\/\d+\/workers$/]];

  var _iteratorNormalCompletion = true;
  var _didIteratorError = false;
  var _iteratorError = undefined;

  try {
    for (var _iterator = rules[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
      var rule = _step.value;

      if (rule[1].test(url)) {
        if (typeof rule[0] != 'function') throw new Error('invalid route method \'' + rule[1] + '\'');
        rule[0]();
        break;
      }
    }
  } catch (err) {
    _didIteratorError = true;
    _iteratorError = err;
  } finally {
    try {
      if (!_iteratorNormalCompletion && _iterator.return) {
        _iterator.return();
      }
    } finally {
      if (_didIteratorError) {
        throw _iteratorError;
      }
    }
  }
};

var router = exports.router = new Router();

},{"./controllers/organization_create":2,"./controllers/organization_workers":3}]},{},[1]);

//# sourceMappingURL=admin_browserify.js.map

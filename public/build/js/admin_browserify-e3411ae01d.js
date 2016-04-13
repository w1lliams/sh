(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _router = require('./router');

var _router2 = _interopRequireDefault(_router);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

$(function () {
  console.log('ready');
  var router = new _router2.default();

  // добавляем всем ajax запросам csrf token
  $.ajaxSetup({
    headers: {
      'X-CSRF-Token': window._token
    }
  });

  // плагин для селектов
  $('.multiselect').multiselect({
    nonSelectedText: 'Выберите значение',
    numberDisplayed: 1
  });
});

},{"./router":8}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

exports.default = new (function () {
  function _class() {
    _classCallCheck(this, _class);
  }

  _createClass(_class, [{
    key: 'index',

    /**
     * Страница создания организации
     */
    value: function index() {
      $('.duplicateForm').click(this._cloneFormField);
      $('input[name=address]').on('change', this._onChangeAddress);
    }

    /**
     * При изменении адреса парсим почтовый индекс и город
     * @param e
     * @private
     */

  }, {
    key: '_onChangeAddress',
    value: function _onChangeAddress(e) {
      var $address = $(e.currentTarget);
      //  если есть почтовый индекс в адресе вставляем его в соответствующее поле
      var matches = /^(\d+)/.exec($address.val());
      if (matches) $('input[name=postCode]').val(matches[1]);

      // ищем город и выбираем его в выпадающем списке
      matches = /(?:місто|м\.)\s*([а-яїґ]+)/i.exec($address.val());
      if (matches) {
        var $select = $('select[name=city]');
        var $option = $select.find('option').filter(function (key, option) {
          return $(option).text().toLowerCase() == matches[1].toLowerCase();
        });

        $option.attr('selected', 'true');
        $select.multiselect('refresh');
      }
    }

    /**
     * Дублирование поля ввода
     * @param e
     * @private
     */

  }, {
    key: '_cloneFormField',
    value: function _cloneFormField(e) {
      var $parent = $(e.target.parentNode);
      $parent.prepend($parent.find('input:first').clone().val(''));
    }
  }]);

  return _class;
}())();

},{}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

exports.default = new (function () {
  function _class() {
    _classCallCheck(this, _class);
  }

  _createClass(_class, [{
    key: 'index',

    /**
     * Страница со списком организаций
     */
    value: function index() {
      // быстрый поиск по названию и ЕДРПОУ
      var typeahead = function typeahead(name, field) {
        var $tp = $('input[name=' + name + ']').typeahead({
          highlight: true,
          classNames: { dataset: 'dropdown-menu tt-dataset' }
        }, {
          async: true,
          limit: 7,
          display: field,
          templates: {
            notFound: function notFound() {
              return 'Ничего не найдено';
            },
            pending: function pending() {
              return 'Загружаем...';
            }
          },

          source: _.debounce(function (query, callback, asyncCallback) {
            var data = {};
            data[name] = query;

            $.ajax({
              url: '/admin/api/organization/search',
              data: data
            }).then(asyncCallback);
          }, 500)
        });

        $tp.bind('typeahead:select', function (e, suggestion) {
          window.open('/admin/organization/' + suggestion.id + '/edit');
        });
      };

      typeahead('name', 'fullName');
      typeahead('edrpou', 'edrpou');
      typeahead('chief', 'fullName');

      $('.show-department-btn').click(this._showDepartments.bind(this));
      $('.show-all-departments').click(this._showAllDepartments.bind(this));
    }

    /**
     * Выводим подразделения
     * @param e
     * @private
     */

  }, {
    key: '_showDepartments',
    value: function _showDepartments(e) {
      var organizationId = $(e.currentTarget).data('organization');
      $('.department' + organizationId).fadeToggle('fast');
    }

    /**
     * Раскрываем все подразделения на странице
     * @private
     */

  }, {
    key: '_showAllDepartments',
    value: function _showAllDepartments() {
      var $el = $('tr.department');
      if ($($el[0]).is(':visible')) $('tr.department').fadeOut('fast');else $('tr.department').fadeIn('fast');
    }
  }]);

  return _class;
}())();

},{}],4:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _helpers = require('../helpers');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Controller = function () {
  function Controller() {
    _classCallCheck(this, Controller);

    this.clear();
    this.CHECK_SYMBOLS = /[^АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя№ "'(),-=.\/0123456789;:I]/;
    this.CHECK_LETTERS = /[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]{2,}/;
  }

  _createClass(Controller, [{
    key: 'clear',
    value: function clear() {
      this.errors = {};
    }

    /**
     * Страница импорта файла работников
     */

  }, {
    key: 'index',
    value: function index() {
      $('input[name=file]').on('change', this._onSelectFile.bind(this));
      $('.done').click(this._saveWorkers.bind(this));
    }

    /**
     * Сохраняем ошибки в одном месте
     * @param str
     * @param line
     * @param symbol
     */

  }, {
    key: 'addError',
    value: function addError(str, line) {
      var symbol = arguments.length <= 2 || arguments[2] === undefined ? null : arguments[2];

      if (!this.errors[line]) this.errors[line] = [];
      this.errors[line].push([str, symbol]);
    }

    /**
     *
     * @returns {Promise}
     * @private
     */

  }, {
    key: '_saveWorkers',
    value: function _saveWorkers() {
      var _this = this;

      return $.ajax({
        url: '/admin/api/organization/' + this.originOrganization.id + '/push_workers',
        method: 'post',
        data: {
          workers: this.workers,
          date: this.organization.date
        },
        success: function success() {
          window.location.href = '/admin/organization/' + _this.originOrganization.id + '/workers';
        }
      });
    }

    /**
     * Пользователь выбрал файл
     * @param e
     * @private
     */

  }, {
    key: '_onSelectFile',
    value: function _onSelectFile(e) {
      var _this2 = this;

      this.clear();
      var file = e.target.files[0];
      if (!file) return;

      // вырезаем ЕДРПОУ из нахвания файла
      var matches = /^(\d+)\D/.exec(file.name);
      if (!matches) return alert('Название файла должно содержать ЕДРПОУ номер организации');
      var edrpou = matches[1];

      _helpers.helpers.showPreloader();
      var fileReader = new FileReader();
      fileReader.onload = function (e) {
        _this2.lines = e.target.result.split("\n");

        _this2._checkFile(edrpou).then(function (opt) {
          _this2.organization = opt.organization;
          _this2.workers = opt.workers;
          _this2.fioErrors = opt.fioErrors;

          return Promise.resolve();
        }).then(_this2._showFile.bind(_this2)).then(_this2._showOrganization.bind(_this2)).then(_this2._checkErrors.bind(_this2)).then(_helpers.helpers.hidePreloader).catch(_helpers.helpers.hidePreloader);
      };
      fileReader.readAsText(file, 'cp1251');
    }

    /**
     * Достаем с сервера организацию по ЕДРПОУ из файла и выводим ее,
     * чтобы можератор мог убедиться что редактирует нужную организацию
     *
     * @returns {Promise}
     * @private
     */

  }, {
    key: '_showOrganization',
    value: function _showOrganization() {
      var _this3 = this;

      return new Promise(function (resolve, reject) {
        $.ajax({
          url: '/admin/api/organization/search',
          method: 'get',
          data: {
            edrpou: _this3.organization.edrpou
          },
          success: function success(data) {
            var $el = $('.organization');
            $el.html('');

            if (!data || data.length == 0) {
              alert('<p class="alert alert-danger">Организация с ЕДРПОУ ' + _this3.organization.edrpou + ' не найдена</p>');
              reject();
            } else {
              _this3.originOrganization = data[0];
              $el.html('[' + _this3.organization.edrpou + '] ' + _this3.organization.text + ' = [' + _this3.originOrganization.edrpou + '] ' + _this3.originOrganization.fullName);
              resolve();
            }
          }
        });
      });
    }

    /**
     * Проверяем, если есть ошибки, сообщаем пользователю.
     * Если нет то выводим кнопку "сохранить работников"
     *
     * @returns {*}
     * @private
     */

  }, {
    key: '_checkErrors',
    value: function _checkErrors() {
      if (_.isEmpty(this.errors)) {
        $('.done').show();
        return Promise.resolve();
      }

      $('.done').hide();
      alert('В файле есть ошибки');
      return Promise.reject();
    }

    /**
     * Добавление работников в БД
     * @param opt
     * @returns {Promise}
     * @private
     */

  }, {
    key: '_addWorkers',
    value: function _addWorkers(opt) {
      return new Promise(function (resolve, reject) {
        if (opt.errors && opt.errors.length > 0) return reject('file has errors');

        console.log(opt.edrpou);
      });
    }

    /**
     * И тут начинается пиздорез с проверкой
     * @param edrpou
     * @private
     */

  }, {
    key: '_checkFile',
    value: function _checkFile(edrpou) {
      var _this4 = this;

      return new Promise(function (resolve, reject) {
        var organization = _this4._parseFirstLine(_this4.lines[0]);
        organization.edrpou = edrpou;

        // получаем сотрудников
        var workers = { main: { workers: [] } },
            currentCategory = null,
            currentSubCategory = null,
            matches = void 0;

        for (var i = 1; i < _this4.lines.length; i++) {
          var line = _this4.lines[i].trim();
          if (line.length == 0) continue;

          // проверяем на неразрещенные символы
          matches = _this4.CHECK_SYMBOLS.exec(line);
          if (matches) {
            _this4.addError('Неразрещенный символ', i, matches.index);
          }

          // =категория
          matches = /^=([^=]+)$/.exec(line);
          if (matches) {
            if (currentCategory) _this4.addError('Открытие категории, но предыдущая еще не закрылась', i);
            currentCategory = matches[1];
            continue;
          }

          // закрытие категории "=="
          if (/^\s*==\s*$/.test(line)) {
            if (!currentCategory) _this4.addError('Закрытие категории, но открытия не было', i);
            currentCategory = null;
            continue;
          }

          // -подкатегория
          matches = /^-([^-].*)$/.exec(line);
          if (matches) {
            if (currentSubCategory) _this4.addError('Открытие подкатегории, но предыдущая еще не закрылась', i);
            if (!currentCategory) _this4.addError('Открытие подкатегории, но родительская категория не была найдена', i);
            currentSubCategory = matches[1];
            continue;
          }

          // закрытие подкатегории "--"
          if (/^\s*--\s*$/.test(line)) {
            if (!currentSubCategory) _this4.addError('Закрытие подкатегории, но открытия не было', i);
            currentSubCategory = null;
            continue;
          }

          // парсим работников
          var cat = currentCategory ? currentCategory : 'main';
          if (!workers[cat]) workers[cat] = {
            workers: [],
            sub: {}
          };
          var curCategoryWorkers = void 0;

          if (currentSubCategory) {
            if (!workers[cat].sub[currentSubCategory]) workers[cat].sub[currentSubCategory] = [];
            curCategoryWorkers = workers[cat].sub[currentSubCategory];
          } else curCategoryWorkers = workers[cat].workers;

          _this4._parseWorker(line, {
            workers: curCategoryWorkers,
            line: i
          });
        }

        if (currentCategory) _this4.addError('Неверно открыты/закрыты категории.', _this4.lines.length);

        // проверяем работников по БД
        _this4._checkWorkers(workers).then(function (fioErrors) {
          resolve({ fioErrors: fioErrors, workers: workers, organization: organization });
        }.bind(_this4));
      });
    }

    /**
     *
     * @param value
     * @returns {Object}
     * @private
     */

  }, {
    key: '_parseFirstLine',
    value: function _parseFirstLine(value) {
      var data = value.split(','),
          date = data[0].trim(),
          workersCount = data[1].trim();

      // проверяем дату
      if (!/^\d{2}\.\d{2}\.\d{4}$/.test(date)) this.addError('Неверный формат даты "' + date + '" (должен быть <11.11.2222>)', 0);

      // кол-во сотрудников
      if (!/^(\d+|-)$/.test(workersCount)) this.addError('Кол-во сотрудников указано неверно "' + workersCount + '" (должно быть число либо <->)', 0);

      return {
        workersCount: parseInt(data[1].trim()),
        text: data.slice(2).join(',').trim(),
        date: date
      };
    }

    /**
     *
     * @param value
     * @param opt
     * @private
     */

  }, {
    key: '_parseWorker',
    value: function _parseWorker(value, opt) {
      var matches = /([^,]+),(.*)$/.exec(value);
      if (!matches) {
        this.addError('Невозможно распознать ФИО и должность', opt.line);
        return;
      }

      var position = matches[2].trim();
      if (!this.CHECK_LETTERS.test(position)) {
        this.addError('Невозможно распознать ФИО и должность', opt.line);
        return;
      }

      opt.workers.push({ fio: matches[1].trim(), position: position });
    }

    /**
     * Проверяем ФИО по БД
     * @param workers
     * @returns {*}
     * @private
     */

  }, {
    key: '_checkWorkers',
    value: function _checkWorkers(workers) {
      // отправляем на сервер только массив ФИО
      var data = [];
      for (var i in workers) {
        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = undefined;

        try {
          for (var _iterator = workers[i].workers[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var _worker = _step.value;

            data.push(_worker.fio);
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

        for (var j in workers[i].sub) {
          var _iteratorNormalCompletion2 = true;
          var _didIteratorError2 = false;
          var _iteratorError2 = undefined;

          try {
            for (var _iterator2 = workers[i].sub[j][Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
              var worker = _step2.value;

              data.push(worker.fio);
            }
          } catch (err) {
            _didIteratorError2 = true;
            _iteratorError2 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion2 && _iterator2.return) {
                _iterator2.return();
              }
            } finally {
              if (_didIteratorError2) {
                throw _iteratorError2;
              }
            }
          }
        }
      }

      return $.ajax({
        url: '/admin/api/worker/check_new_workers',
        method: 'post',
        data: {
          workers: JSON.stringify(data)
        }
      });
    }

    /**
     * Выводим файл и ошибки проверки файла
     * @private
     */

  }, {
    key: '_showFile',
    value: function _showFile() {
      var _this5 = this;

      var $el = $('.file');
      $el.html('');

      // выводим построчно весь файл

      var _loop = function _loop(i) {
        var line = _this5.lines[i],
            lineErrors = void 0;

        // ошибки в этой строке
        if (_this5.errors[i]) {
          lineErrors = _this5.errors[i].map(function (err) {
            // если есть позиция символа, выделяем его
            if (err[1]) {
              var s = line.substr(err[1], 1);
              line = line.substr(0, err[1]) + ('<span class="error-sym">' + s + '</span>') + line.substr(err[1] + 1);
            }
            return err[0] + '<br>';
          });
        }

        // если в строке работник и по нему есть ошибки, выводим их
        if (i != 0 && !/^(--|==)/.test(line) && line.length != 0) {
          var matches = /([^,]+),/.exec(line);
          if (matches && _this5.fioErrors.indexOf(matches[1]) >= 0) {
            line = '<span class="warning-sym">' + matches[1] + '</span>' + line.substr(matches[1].length);
          }
        }

        $el.append(line + '<br>');
        if (lineErrors) $el.append('<p class="alert alert-danger">' + lineErrors + '</p>');
      };

      for (var i = 0; i < this.lines.length; i++) {
        _loop(i);
      }

      return Promise.resolve();
    }
  }]);

  return Controller;
}();

exports.default = new Controller();

},{"../helpers":7}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

exports.default = new (function () {
  function _class() {
    _classCallCheck(this, _class);
  }

  _createClass(_class, [{
    key: 'fio',

    /**
     * Страница с переводом ФИО
     */
    value: function fio() {
      $('.page-fio-translate button').click(this._translate.bind(this));
    }
  }, {
    key: '_translate',
    value: function _translate() {
      return $.ajax({
        url: '/admin/api/translate/fio',
        method: 'post',
        data: {
          firstName: $('#i').val(),
          lastName: $('#f').val(),
          middleName: $('#o').val()
        },
        success: function success(data) {
          $('#i2').val(data.firstName);
          $('#f2').val(data.lastName);
          $('#o2').val(data.middleName);
        }
      });
    }
  }]);

  return _class;
}())();

},{}],6:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

exports.default = new (function () {
  function _class() {
    _classCallCheck(this, _class);
  }

  _createClass(_class, [{
    key: 'index',
    value: function index(orgnaization, snapshot) {
      $('.worker').click(this.editWorker.bind(this));
      $('.department-name').click(this.editDepartment.bind(this, snapshot));
    }

    /**
     * Редактирование сотрудника
     * @param e
     * @returns {*}
     */

  }, {
    key: 'editWorker',
    value: function editWorker(e) {
      var $el = $(e.currentTarget),
          source = $el.text().split(','),
          data = {};

      // спрашиваем ФИО
      var answer = void 0;
      if (_.isEmpty(answer = prompt('ФИО', source[0]))) return;
      data.fio = answer.trim();

      // спрашиваем должность
      if (_.isEmpty(answer = prompt('должность', source[1].trim()))) return;
      data.position = answer.trim();

      return $.ajax({
        method: 'post',
        url: '/admin/api/worker/' + $el.data('id'),
        data: data,
        success: function success(data) {
          return $el.html(data.fio + ', ' + data.position);
        }
      });
    }

    /**
     * Редактирование название отдела
     * @param snapshot
     * @param e
     */

  }, {
    key: 'editDepartment',
    value: function editDepartment(snapshot, e) {
      var $el = $(e.currentTarget),
          name = $el.text();
      var result = prompt('Отдел', name);
      if (_.isEmpty(result)) return;

      $.ajax({
        method: 'post',
        url: '/admin/api/worker/change_department',
        data: {
          snapshot: snapshot, name: name,
          newName: result.trim(),
          field: $el.data('sub') ? 'subDepartment' : 'department'
        },
        success: function success(data) {
          return $el.html(result);
        }
      });
    }
  }]);

  return _class;
}())();

},{}],7:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var helpers = exports.helpers = {
  showPreloader: function showPreloader() {
    $('body').addClass('preloader');
  },

  hidePreloader: function hidePreloader() {
    $('body').removeClass('preloader');
    return Promise.resolve();
  }
};

},{}],8:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _organization_create = require('./controllers/organization_create');

var _organization_create2 = _interopRequireDefault(_organization_create);

var _organization_workers = require('./controllers/organization_workers');

var _organization_workers2 = _interopRequireDefault(_organization_workers);

var _workers = require('./controllers/workers');

var _workers2 = _interopRequireDefault(_workers);

var _organization_list = require('./controllers/organization_list');

var _organization_list2 = _interopRequireDefault(_organization_list);

var _translate = require('./controllers/translate');

var _translate2 = _interopRequireDefault(_translate);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Router =
/**
 * Прогоняем роутфы и выполняем первый подходящий
 * @param url
 */
function Router() {
  var url = arguments.length <= 0 || arguments[0] === undefined ? window.location.pathname : arguments[0];

  _classCallCheck(this, Router);

  url = url.replace(/\/$/, '');
  // роуты добавлять здесь
  var rules = [[_organization_list2.default.index.bind(_organization_list2.default), /admin\/organization$/], [_organization_create2.default.index.bind(_organization_create2.default), /admin\/organization\/(create|\d+\/edit)$/], [_organization_create2.default.index.bind(_organization_create2.default), /admin\/organization\/\d+\/addDepartment$/], [_organization_workers2.default.index.bind(_organization_workers2.default), /admin\/organization\/(\d+)\/addWorkers$/], [_workers2.default.index.bind(_workers2.default), /admin\/organization\/(\d+)\/snapshot\/(\d+)$/], [_translate2.default.fio.bind(_translate2.default), /translate$/]];

  var matches = void 0;
  var _iteratorNormalCompletion = true;
  var _didIteratorError = false;
  var _iteratorError = undefined;

  try {
    for (var _iterator = rules[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
      var rule = _step.value;

      if (matches = rule[1].exec(url)) {
        if (typeof rule[0] != 'function') throw new Error('invalid route method \'' + rule[1] + '\'');
        rule[0].apply(rule, _toConsumableArray(matches.slice(1)));
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

exports.default = Router;

},{"./controllers/organization_create":2,"./controllers/organization_list":3,"./controllers/organization_workers":4,"./controllers/translate":5,"./controllers/workers":6}]},{},[1]);

//# sourceMappingURL=admin_browserify.js.map
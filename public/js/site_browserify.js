(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Базовый контроллер
 */

var Controller = exports.Controller = function () {
  function Controller() {
    _classCallCheck(this, Controller);

    this.widgets = [];
  }

  /**
   * Запускаем виджет
   * @param WidgetClass
   * @param options
   */


  _createClass(Controller, [{
    key: "widget",
    value: function widget(WidgetClass) {
      var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

      // сохраняем все объекты виджетов, чтобы потом можно было вызвать у всех destroy
      this.widgets.push(new WidgetClass(options));
    }

    /**
     * Вызывается роутером при смене роута,
     * удаляем все виджеты
     */

  }, {
    key: "clear",
    value: function clear() {
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = this.widgets[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var widget = _step.value;

          widget.destroy();
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
    }
  }]);

  return Controller;
}();

},{}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Базовый роутер
 */

var Router = exports.Router = function (_EventEmitter) {
  _inherits(Router, _EventEmitter);

  /**
   * @param rules
   */

  function Router() {
    var rules = arguments.length <= 0 || arguments[0] === undefined ? [] : arguments[0];

    _classCallCheck(this, Router);

    var _this = _possibleConstructorReturn(this, Object.getPrototypeOf(Router).call(this));

    _this.rules = rules;
    return _this;
  }

  /**
   * Запускаем роутер (вызывать один раз)
   */


  _createClass(Router, [{
    key: 'start',
    value: function start() {
      this._findRoute();
    }

    /**
     * Меняем адрес и переходим
     * @param url
     * @param opt
     */

  }, {
    key: 'navigate',
    value: function navigate(url, opt) {
      if (!/^\/[a-z]{2}\//.test(url) && !/^\/[a-z]{2}$/.test(url)) url = '/' + window.locale + url;
      history.pushState(null, '', url);
      this.changeUrl(opt);
    }

    /**
     * Вызывается когда поменялся URL
     */

  }, {
    key: 'changeUrl',
    value: function changeUrl(opt) {
      var _this2 = this;

      return $.ajax(_.extend(opt, {
        url: location.pathname + location.search,
        success: function success(data) {
          $('#content').html(data);
          _this2._findRoute();
          $(window).scrollTop(0);
          $('.button-collapse').sideNav('hide');
          $('#content .dropdown-button').dropdown();
        },

        error: function error(xhr) {
          $('#content').html(xhr.responseText);
          _this2._findRoute();
          $(window).scrollTop(0);
          $('.button-collapse').sideNav('hide');
        }
      }));
    }

    /**
     * Запускаем роут который соответствует текущего адресу
     * @private
     */

  }, {
    key: '_findRoute',
    value: function _findRoute() {
      var url = location.pathname;
      if (/^\/[a-z]{2}(\/.*|)$/.test(url)) url = url.replace(/^\/[a-z]{2}/, '');
      url = url.replace(/\/$/, '');

      var matches = void 0;
      // запускаем медом clear у контроллера который сейчас работает
      // перед тем как запустить новый метод контроллера
      if (this.lastController) this.lastController.clear();

      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = this.rules[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var rule = _step.value;

          if (matches = rule[2].exec(url)) {
            if (typeof rule[0][rule[1]] != 'function') throw new Error('invalid controller method \'' + rule[1] + '\'');

            // запуска метод контроллера
            rule[0][rule[1]].apply(rule[0], matches.slice(1));
            // запоминаем какой контроллер запущен
            this.lastController = rule[0];

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

      this.emit('change', url);
    }
  }]);

  return Router;
}(EventEmitter);

},{}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Widget = exports.Widget = function () {
  /**
   * Вешаем события из _events() и достаем элементы из _ui()
   * @param options
   */

  function Widget(options) {
    var _this = this;

    _classCallCheck(this, Widget);

    this.namespace = this.namespace || this.constructor.name + Math.random().toString().substr(2);
    this.options = options;

    // находим все элементы для виджета
    this.ui = {};
    if (this._ui) {
      _.forEach(this._ui(), function (val, key) {
        _this.ui[key] = $(val);
      });
    }

    // подключаем все события
    this.events = [];
    if (this._events) {
      _.forEach(this._events(), function (callback, event) {
        var matches = /^([-_a-z]+) (.*)$/i.exec(event);
        if (!matches) throw 'event not match "<event> <selector|ui.element>"';

        var eventName = matches[1] + '.' + _this.namespace;
        var selector = matches[2];

        // смотрим если передали вместо селектора элемент из this.ui
        var ui = /^ui\.(.*)$/.exec(matches[2]);
        if (ui) selector = _this.ui[ui[1]];
        callback = typeof callback == 'function' ? callback : _this[callback];
        $(selector).on(eventName, callback.bind(_this));
        _this.events.push(selector);
      });
    }

    // live события
    if (this._live) {
      _.forEach(this._live(), function (callback, event) {
        var matches = /^([-_a-z]+) (.*)$/i.exec(event);
        if (!matches) throw 'event not match "<event> <selector|ui.element>"';

        var eventName = matches[1] + '.' + _this.namespace;
        var selector = matches[2];

        callback = typeof callback == 'function' ? callback : _this[callback];
        $(document).on(eventName, selector, callback.bind(_this));
      });
    }
  }

  /**
   * Отключаем события
   */


  _createClass(Widget, [{
    key: 'destroy',
    value: function destroy() {
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = this.events[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var e = _step.value;

          $(e).off('.' + this.namespace);
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

      $(document).off('.' + this.namespace);
    }
  }]);

  return Widget;
}();

},{}],4:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.app = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _router = require('./router');

var _search = require('./widgets/search');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var app = exports.app = new (function () {
  function _class() {
    _classCallCheck(this, _class);
  }

  _createClass(_class, [{
    key: 'init',
    value: function init() {
      _router.router.start();
      this._initToken();
      new _search.SearchWidget();
    }
  }, {
    key: '_initToken',
    value: function _initToken() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
    }
  }]);

  return _class;
}())();

$(function () {
  // run app
  app.init();
});

},{"./router":6,"./widgets/search":7}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.miscController = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _controller = require('../../base/controller');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var MiscController = function (_Controller) {
  _inherits(MiscController, _Controller);

  function MiscController() {
    _classCallCheck(this, MiscController);

    return _possibleConstructorReturn(this, Object.getPrototypeOf(MiscController).apply(this, arguments));
  }

  _createClass(MiscController, [{
    key: 'home',

    /**
     * Главная страница
     */
    value: function home() {}
  }]);

  return MiscController;
}(_controller.Controller);

var miscController = exports.miscController = new MiscController();

},{"../../base/controller":1}],6:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.router = undefined;

var _router = require('../base/router');

var _misc = require('./controllers/misc');

var router = exports.router = new _router.Router([[_misc.miscController, 'home', /^$/]]);

},{"../base/router":2,"./controllers/misc":5}],7:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.SearchWidget = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _widget = require('../../base/widget');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Виджет поиска на главной странице
 */

var SearchWidget = exports.SearchWidget = function (_Widget) {
  _inherits(SearchWidget, _Widget);

  function SearchWidget() {
    _classCallCheck(this, SearchWidget);

    var _this = _possibleConstructorReturn(this, Object.getPrototypeOf(SearchWidget).apply(this, arguments));

    $(document).on('click', function () {
      _this.ui.results.hide();
    });
    return _this;
  }

  /**
   * ui
   * @return {Object}
   */


  _createClass(SearchWidget, [{
    key: '_ui',
    value: function _ui() {
      return {
        form: '#search-form',
        input: '#search-form input[name=q]',
        results: '#search-form .results'
      };
    }

    /**
     * events
     * @return {Object}
     */

  }, {
    key: '_events',
    value: function _events() {
      return {
        'change ui.input': 'search',
        'submit ui.form': 'search',
        'click ui.results': function clickUiResults(e) {
          return e.stopPropagation();
        }
      };
    }
  }, {
    key: 'search',
    value: function search(e) {
      e.preventDefault();
      $.ajax({
        url: '/api/search',
        method: 'post',
        data: { q: this.ui.input.val() },
        success: this.renderResults.bind(this)
      });
    }
  }, {
    key: 'renderResults',
    value: function renderResults(data) {
      // предприятия
      var html = '<div class="category text-muted"><div class="head">Предприятия:</div>';
      html += _.map(data.organizations, function (organization) {
        return '<a href="/organization/' + organization.id + '">\n        <span class="title">' + organization.fullName + '</span>\n        <span class="sub-title">' + (organization.address || '') + '</span>\n      </a>';
      }).join('');
      html += '</div>';

      html += '<div class="category text-muted"><div class="head">Работники:</div>';
      html += _.map(data.workers, function (worker) {
        return '<a href="/worker/' + worker.id + '">\n        <span class="title">' + worker.fio + '</span>\n        <span class="sub-title">' + worker.position + '</span>\n      </a>';
      }).join('');
      html += '</div>';

      this.ui.results.html(html).show();
    }
  }]);

  return SearchWidget;
}(_widget.Widget);

},{"../../base/widget":3}]},{},[4]);

//# sourceMappingURL=site_browserify.js.map

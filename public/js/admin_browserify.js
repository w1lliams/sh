(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _router = require('./router');

$('.multiselect').multiselect({
  nonSelectedText: 'Выберите значение'
});

},{"./router":3}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
var organization = exports.organization = {
  /**
   * Страница создания организации
   */
  create: function create() {
    $('.duplicateForm').click(function (e) {
      var $parent = $(e.target.parentNode);
      $parent.prepend($parent.find('input:first').clone().val(''));
    });
  }
};

},{}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.router = undefined;

var _organization = require('./controllers/organization');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Router =
/**
 * Прогоняем роутфы и выполняем первый подходящий
 * @param url
 */
function Router() {
  var url = arguments.length <= 0 || arguments[0] === undefined ? window.location.pathname : arguments[0];

  _classCallCheck(this, Router);

  console.log('asdas');
  // роуты добавлять здесь
  var rules = [[_organization.organization.create, /admin\/organization\/create$/]];

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

},{"./controllers/organization":2}]},{},[1]);

//# sourceMappingURL=admin_browserify.js.map

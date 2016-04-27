/**
 * Базовый роутер
 */
export class Router extends EventEmitter {
  /**
   * @param rules
   */
  constructor(rules = []) {
    super();
    this.rules = rules;
  }

  /**
   * Запускаем роутер (вызывать один раз)
   */
  start() {
    this._findRoute();
  }

  /**
   * Меняем адрес и переходим
   * @param url
   * @param opt
   */
  navigate(url, opt) {
    if(!/^\/[a-z]{2}\//.test(url) && !/^\/[a-z]{2}$/.test(url))
      url = `/${window.locale}${url}`;
    history.pushState(null, '', url);
    this.changeUrl(opt);
  }

  /**
   * Вызывается когда поменялся URL
   */
  changeUrl(opt) {
    return $.ajax(_.extend(opt, {
      url: location.pathname + location.search,
      success: (data) => {
        $('#content').html(data);
        this._findRoute();
        $(window).scrollTop(0);
        $('.button-collapse').sideNav('hide');
        $('#content .dropdown-button').dropdown();
      },

      error: (xhr) => {
        $('#content').html(xhr.responseText);
        this._findRoute();
        $(window).scrollTop(0);
        $('.button-collapse').sideNav('hide');
      }
    }));
  }

  /**
   * Запускаем роут который соответствует текущего адресу
   * @private
   */
  _findRoute() {
    let url = location.pathname;
    if(/^\/[a-z]{2}(\/.*|)$/.test(url))
      url = url.replace(/^\/[a-z]{2}/, '');
    url = url.replace(/\/$/, '');

    let matches;
    // запускаем медом clear у контроллера который сейчас работает
    // перед тем как запустить новый метод контроллера
    if(this.lastController)
      this.lastController.clear();

    for (let rule of this.rules) {
      if (matches = rule[2].exec(url)) {
        if (typeof rule[0][rule[1]] != 'function')
          throw new Error(`invalid controller method '${rule[1]}'`);

        // запуска метод контроллера
        rule[0][rule[1]].apply(rule[0], matches.slice(1));
        // запоминаем какой контроллер запущен
        this.lastController = rule[0];

        break;
      }
    }

    this.emit('change', url);
  }
}

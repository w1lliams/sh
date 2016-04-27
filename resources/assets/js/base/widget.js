export class Widget {
  /**
   * Вешаем события из _events() и достаем элементы из _ui()
   * @param options
   */
  constructor(options) {
    this.namespace = this.namespace || this.constructor.name + Math.random().toString().substr(2);
    this.options = options;

    // находим все элементы для виджета
    this.ui = {};
    if(this._ui) {
      _.forEach(this._ui(), (val, key) => {
        this.ui[key] = $(val);
      });
    }

    // подключаем все события
    this.events = [];
    if(this._events) {
      _.forEach(this._events(), (callback, event) => {
        const matches = /^([-_a-z]+) (.*)$/i.exec(event);
        if (!matches) throw 'event not match "<event> <selector|ui.element>"';

        const eventName = `${matches[1]}.${this.namespace}`;
        let selector = matches[2];

        // смотрим если передали вместо селектора элемент из this.ui
        const ui = /^ui\.(.*)$/.exec(matches[2]);
        if (ui) selector = this.ui[ui[1]];
        callback = typeof callback == 'function' ? callback : this[callback];
        $(selector).on(eventName, callback.bind(this));
        this.events.push(selector);
      });
    }

    // live события
    if(this._live) {
      _.forEach(this._live(), (callback, event) => {
        const matches = /^([-_a-z]+) (.*)$/i.exec(event);
        if (!matches) throw 'event not match "<event> <selector|ui.element>"';

        const eventName = `${matches[1]}.${this.namespace}`;
        let selector = matches[2];

        callback = typeof callback == 'function' ? callback : this[callback];
        $(document).on(eventName, selector, callback.bind(this));
      });
    }
  }

  /**
   * Отключаем события
   */
  destroy() {
    for(let e of this.events)
      $(e).off(`.${this.namespace}`);

    $(document).off(`.${this.namespace}`);
  }
}
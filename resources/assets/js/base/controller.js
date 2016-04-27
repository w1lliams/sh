/**
 * Базовый контроллер
 */
export class Controller {
  constructor() {
    this.widgets = [];
  }

  /**
   * Запускаем виджет
   * @param WidgetClass
   * @param options
   */
  widget(WidgetClass, options = {}) {
    // сохраняем все объекты виджетов, чтобы потом можно было вызвать у всех destroy
    this.widgets.push(new WidgetClass(options));
  }

  /**
   * Вызывается роутером при смене роута,
   * удаляем все виджеты
   */
  clear() {
    for(let widget of this.widgets)
      widget.destroy();
  }
}
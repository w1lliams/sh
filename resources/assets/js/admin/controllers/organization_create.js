export default new class {
  /**
   * Страница создания организации
   */
  index() {
    $('.duplicateForm').click(this._cloneFormField);
    $('input[name=address]').on('change', this._onChangeAddress);
  }

  /**
   * При изменении адреса парсим почтовый индекс и город
   * @param e
   * @private
   */
  _onChangeAddress(e) {
    const $address = $(e.currentTarget);
    //  если есть почтовый индекс в адресе вставляем его в соответствующее поле
    let matches = /^(\d+)/.exec($address.val());
    if(matches) $('input[name=postCode]').val(matches[1]);

    // ищем город и выбираем его в выпадающем списке
    matches = /(?:місто|м\.)\s*([а-яїґ]+)/i.exec($address.val());
    if(matches) {
      const $select = $('select[name=city]');
      const $option = $select.find('option').filter((key, option) => {
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
  _cloneFormField(e) {
    const $parent = $(e.target.parentNode);
    $parent.prepend($parent.find('input:first').clone().val(''));
  }
};
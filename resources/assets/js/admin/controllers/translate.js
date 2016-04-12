export default new class {
  /**
   * Страница с переводом ФИО
   */
  fio() {
    $('.page-fio-translate button').click(this._translate.bind(this));
  }

  _translate() {
    return $.ajax({
      url: '/admin/api/translate/fio',
      method: 'post',
      data: {
        firstName:  $('#i').val(),
        lastName:   $('#f').val(),
        middleName: $('#o').val()
      },
      success: (data) => {
        $('#i2').val(data.firstName);
        $('#f2').val(data.lastName);
        $('#o2').val(data.middleName);
      }
    });
  }
};

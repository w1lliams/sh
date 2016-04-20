export default new class {
  /**
   * Страница со списком запросов
   */
  index() {
    $('.inquiry').click((e) => {
      const $el = $(e.currentTarget);
      console.log($el.data('note'));
      const newNote = prompt('Примечание к запросу:', $el.data('note'));
      if(newNote === null) return;

      return $.ajax({
        url: `/admin/api/organization_inquiry/${$el.data('id')}`,
        method: 'post',
        data: {note: newNote},
        success: location.reload.bind(location)
      });
    });
  }
};
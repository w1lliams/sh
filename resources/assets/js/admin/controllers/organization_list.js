export default new class {
  /**
   * Страница со списком организаций
   */
  index() {
    // быстрый поиск по названию и ЕДРПОУ
    const typeahead = (name, field) => {
      const $tp = $(`input[name=${name}]`).typeahead(
        {
          highlight: true,
          classNames: { dataset: 'dropdown-menu tt-dataset' }
        },
        {
          async: true,
          limit: 7,
          display: field,
          templates: {
            notFound: () => 'Ничего не найдено',
            pending: () => 'Загружаем...'
          },

          source: _.debounce((query, callback, asyncCallback) => {
            const data = {};
            data[name] = query;

            $.ajax({
              url: '/admin/api/organization/search',
              data: data
            }).then(asyncCallback);
          }, 500)
        }
      );

      $tp.bind('typeahead:select', function(e, suggestion) {
        window.open(`/admin/organization/${suggestion.id}/edit`);
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
  _showDepartments(e) {
    const organizationId = $(e.currentTarget).data('organization');
    $(`.department${organizationId}`).fadeToggle('fast');
  }

  /**
   * Раскрываем все подразделения на странице
   * @private
   */
  _showAllDepartments() {
    const $el = $('tr.department');
    if($($el[0]).is(':visible'))
      $('tr.department').fadeOut('fast');
    else $('tr.department').fadeIn('fast');
  }
};
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
  }
};
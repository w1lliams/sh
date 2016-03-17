export default new class {
  /**
   * Страница со списком организаций
   */
  index() {
    // быстрый поиск по названию и ЕДРПОУ
    const typeahead = (name, field) => {
      const $tp = $(`input[name=${name}]`).typeahead({
        classNames: {
          dataset: 'dropdown-menu tt-dataset'
        }
      }, {
        display: field,
        source: (query, callback, asyncCallback) => {
          const data = {};
          data[name] = query;

          $.ajax({
            url: '/admin/organization/search',
            data: data
          }).then(asyncCallback);
        }
      });

      $tp.bind('typeahead:select', function(e, suggestion) {
        window.open(`/admin/organization/${suggestion.id}/edit`);
      });
    };

    typeahead('name', 'fullName');
    typeahead('edrpou', 'edrpou');
  }
};
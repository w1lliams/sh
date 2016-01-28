export let organization = {
  /**
   * Страница создания организации
   */
  create: () => {
    $('.duplicateForm').click(e => {
      let $parent = $(e.target.parentNode);
      $parent.prepend($parent.find('input:first').clone().val(''));
    });
  }
};
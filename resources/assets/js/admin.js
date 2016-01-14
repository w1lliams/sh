$('.multiselect').multiselect({
  nonSelectedText: 'Выберите значение'
});

var formHelper = {
  duplicate: function (selector, options) {
    options = options || {};
    var $el = $(selector);
    if($el.length == 0) return;
    $el.parent()[options.prepend ? 'prepend' : 'append']($el.clone());
  }
};
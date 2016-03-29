import Router from './router';

$(() => {
  console.log('ready');
  const router = new Router;

  // добавляем всем ajax запросам csrf token
  $.ajaxSetup({
    headers: {
      'X-CSRF-Token': window._token
    }
  });

  // плагин для селектов
  $('.multiselect').multiselect({
    nonSelectedText: 'Выберите значение',
    numberDisplayed: 1
  });
});
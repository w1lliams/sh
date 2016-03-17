import Router from './router';

$(() => {
  console.log('ready');
  const router = new Router;

  $('.multiselect').multiselect({
    nonSelectedText: 'Выберите значение',
    numberDisplayed: 1
  });
});
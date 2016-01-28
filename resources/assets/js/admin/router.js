import {organization} from './controllers/organization';

class Router {
  /**
   * Прогоняем роутфы и выполняем первый подходящий
   * @param url
   */
  constructor(url = window.location.pathname) {
    console.log('asdas');
    // роуты добавлять здесь
    let rules = [
      [organization.create, /admin\/organization\/create$/]
    ];

    for(let rule of rules) {
      if(rule[1].test(url)) {
        if(typeof rule[0] != 'function')
          throw new Error(`invalid route method '${rule[1]}'`);
        rule[0]();
        break;
      }
    }
  }
}

export let router = new Router();
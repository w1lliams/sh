import organizationCreate from './controllers/organization_create';
import organizationWorkers from './controllers/organization_workers';
import organizationList from './controllers/organization_list';

export default class Router {
  /**
   * Прогоняем роутфы и выполняем первый подходящий
   * @param url
   */
  constructor(url = window.location.pathname) {
    // роуты добавлять здесь
    let rules = [
      [organizationList.index.bind(organizationList), /admin\/organization$/],
      [organizationCreate.index.bind(organizationCreate), /admin\/organization\/(create|\d+\/edit)$/],
      [organizationWorkers.index.bind(organizationWorkers), /admin\/organization\/\d+\/workers$/]
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
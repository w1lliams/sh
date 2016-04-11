import organizationCreate from './controllers/organization_create';
import addWorkers from './controllers/organization_workers';
import workers from './controllers/workers';
import organizationList from './controllers/organization_list';
import translate from './controllers/translate';

export default class Router {
  /**
   * Прогоняем роутфы и выполняем первый подходящий
   * @param url
   */
  constructor(url = window.location.pathname) {
    url = url.replace(/\/$/, '');
    // роуты добавлять здесь
    let rules = [
      [organizationList.index.bind(organizationList), /admin\/organization$/],
      [organizationCreate.index.bind(organizationCreate), /admin\/organization\/(create|\d+\/edit)$/],
      [organizationCreate.index.bind(organizationCreate), /admin\/organization\/\d+\/addDepartment$/],
      [addWorkers.index.bind(addWorkers), /admin\/organization\/(\d+)\/addWorkers$/],
      [workers.index.bind(workers), /admin\/organization\/(\d+)\/snapshot\/(\d+)$/],
      [translate.fio.bind(translate), /admin\/translate\/fio$/]
    ];

    let matches;
    for(let rule of rules) {
      if(matches = rule[1].exec(url)) {
        if(typeof rule[0] != 'function')
          throw new Error(`invalid route method '${rule[1]}'`);
        rule[0](...matches.slice(1));
        break;
      }
    }
  }
}
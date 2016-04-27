import {Controller} from '../../base/controller';
import {SearchWidget} from '../widgets/search';

class MiscController extends Controller {
  /**
   * Главная страница
   */
  home() {
    this.widget(SearchWidget);
  }
}

export const miscController = new MiscController;
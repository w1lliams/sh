import {Router} from '../base/router';
import {miscController} from './controllers/misc';

export const router = new Router([
  [miscController, 'home', /^$/],
]);
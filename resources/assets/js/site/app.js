import {router} from './router';
import {SearchWidget} from './widgets/search';

export const app = new class {
  init() {
    router.start();
    this._initToken();
    new SearchWidget(); 
  }

  _initToken() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
  }
};


$(() => {
  // run app
  app.init();
});

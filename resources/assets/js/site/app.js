import {router} from './router';

export const app = new class {
  init() {
    router.start();
    this._initToken();
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

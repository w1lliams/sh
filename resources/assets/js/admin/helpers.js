export let helpers = {
  showPreloader: () => {
    $('body').addClass('preloader');
  },

  hidePreloader: () => {
    $('body').removeClass('preloader');
    return Promise.resolve();
  },
};
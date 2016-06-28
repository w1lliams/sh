import {Widget} from '../../base/widget';

/**
 * Виджет поиска на главной странице
 */
export class SearchWidget extends Widget {
  constructor() {
    super(...arguments);
    $(document).on('click', () => {
      this.ui.results.hide();
    });
  }

  /**
   * ui
   * @return {Object}
   */
  _ui() {
    return {
      form: '#search-form',
      input: '#search-form input[name=q]',
      results: '#search-form .results'
    };
  }

  /**
   * events
   * @return {Object}
   */
  _events() {
    return {
      'keyup ui.input': _.debounce(this.search.bind(this), 500),
      'submit ui.form': 'search',
      'click ui.results': e => e.stopPropagation(),
      'click ui.form': e => e.stopPropagation()
    };
  }

  search(e) {

    e.preventDefault();
    $.ajax({
      url: '/api/search',
      method: 'post',
      data: {q: this.ui.input.val()},
      success: this.renderResults.bind(this)
    });
  }

  renderResults(data) {
    let html = '';

    // предприятия
    if(data.organizations.length > 0) {
      html += '<div class="category text-muted"><div class="head">Предприятия:</div>';
      html += _.map(data.organizations, (organization) => {
        return `<a href="/organization/${organization.id}">
        <span class="title">${organization.fullName}</span>
        <span class="sub-title">${organization.address || ''}</span>
      </a>`;
      }).join('');
      html += '</div>';
    }

    if(data.workers.length > 0) {
      html += '<div class="category text-muted"><div class="head">Работники:</div>';
      html += _.map(data.workers, (worker) => {
        return `<a href="/worker/${worker.id}">
        <span class="title">${worker.fio}</span>
        <span class="sub-title">${worker.organization.fullName}</span>
      </a>`;
      }).join('');
      html += '</div>';
    }

    if(html.length == 0) {
      html = `
        <h5>Не знайдено жодного працивника або организации</h5>
        Ви можете <a href="/add">додати организацию</a> та найближчим часом ми опубликуемо перелик ии працивникив
      `;
    }

    this.ui.results.html(html).show();
  }
}
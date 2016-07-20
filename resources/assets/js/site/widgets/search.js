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
    if(this.ui.input.val().length < 2) {
      return;
    }

    $.ajax({
      url: '/api/search',
      method: 'post',
      data: {q: this.ui.input.val()},
      success: this.renderResults.bind(this)
    });
  }

  /**
   * Выводим результаты поиска
   * @param organizations
   * @param workers
   */
  renderResults({organizations, workers}) {
    let html = '';

    // предприятия
    if(organizations.length > 0) {
      html += '<div class="category text-muted"><div class="head">Предприятия:</div>';
      html += _.map(organizations, organization => {
        return `<a href="/organization/${organization.id}">
        <span class="title">${this.highlight(organization.fullName)}</span>
        <span class="sub-title">${organization.address || ''}</span>
      </a>`;
      }).join('');
      html += '</div>';
    }

    if(workers.length > 0) {
      html += '<div class="category text-muted"><div class="head">Работники:</div>';
      html += _.map(workers, worker => {
        return `<a href="/worker/${worker.id}">
        <span class="title">${this.highlight(worker.fio)}</span>
        <span class="sub-title">${this.highlight(worker.organization.fullName)}</span>
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

  /**
   * Подсветка совпадения
   * @param {string} val
   * @returns {string}
   */
  highlight(val) {
    const q = this.ui.input.val().trim();
    for(const word of q.split(/\s+/)) {
      val = val.replace(new RegExp(_.escapeRegExp(word), 'gi'), '<span class="highlight">$&</span>')
    }

    return val;
  }
}
import {helpers} from '../helpers';

class Controller {
  constructor() {
    this.clear();
    this.CHECK_SYMBOLS = /[^АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя№ "'(),\-=.\/0123456789;:I]/;
    this.CHECK_LETTERS = /[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]{2,}/;
    this.CHECK_FIO = /[^\-.АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя ]/;
  }

  clear () {
    this.errors = {};
  }

  /**
   * Страница импорта файла работников
   */
  index (organizationId) {
    $('input[name=file]').on('change', this._onSelectFile.bind(this));
    $('.done').click(this._saveWorkers.bind(this, organizationId));
  }

  /**
   * Сохраняем ошибки в одном месте
   * @param str
   * @param line
   * @param symbol
   */
  addError (str, line, symbol = null) {
    if(!this.errors[line])
      this.errors[line] = [];
    this.errors[line].push([str, symbol]);
  }

  /**
   *
   * @returns {Promise}
   * @private
   */
  _saveWorkers (organizationId) {
    return $.ajax({
      url: `/admin/api/organization/${organizationId}/push_workers`,
      method: 'post',
      data: {
        workers: this.workers,
        date: this.organization.date
      },
      success: () => {
        window.location.href = `/admin/organization/${organizationId}/workers`;
      }
    });
  }

  /**
   * Пользователь выбрал файл
   * @param e
   * @private
   */
  _onSelectFile (e) {
    this.clear();
    const file = e.target.files[0];
    if(!file) return;

    // вырезаем ЕДРПОУ из нахвания файла
    let matches = /^(\d+)\D/.exec(file.name);
    if(!matches) return alert('Название файла должно содержать ЕДРПОУ номер организации');
    const edrpou = matches[1];

    helpers.showPreloader();
    const fileReader = new FileReader;
    fileReader.onload = (e) => {
      this.lines = e.target.result.replace(/[\t ]+/g, ' ').split("\n");

      this._checkFile(edrpou).then((opt) => {
        this.organization = opt.organization;
        this.workers = opt.workers;
        this.fioErrors = opt.fioErrors;

        return Promise.resolve();
      })
        .then(this._showFile.bind(this))
        .then(this._showOrganization.bind(this))
        .then(this._checkErrors.bind(this))
        .then(helpers.hidePreloader)
        .catch(helpers.hidePreloader);
    };
    fileReader.readAsText(file, 'cp1251');
  }

  /**
   * Достаем с сервера организацию по ЕДРПОУ из файла и выводим ее,
   * чтобы можератор мог убедиться что редактирует нужную организацию
   *
   * @returns {Promise}
   * @private
   */
  _showOrganization () {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: '/admin/api/organization/search',
        method: 'get',
        data: {
          edrpou: this.organization.edrpou
        },
        success: (data) => {
          const $el = $('.organization');
          $el.html('');

          if(!data || data.length == 0) {
            alert(`<p class="alert alert-danger">Организация с ЕДРПОУ ${this.organization.edrpou} не найдена</p>`);
            reject();
          } else {
            this.originOrganization = data[0];
            $el.html(`[${this.organization.edrpou}] ${this.organization.text} = [${this.originOrganization.edrpou}] ${this.originOrganization.fullName}`);
            resolve();
          }
        }
      });
    });
  }

  /**
   * Проверяем, если есть ошибки, сообщаем пользователю.
   * Если нет то выводим кнопку "сохранить работников"
   *
   * @returns {*}
   * @private
   */
  _checkErrors () {
    if(_.isEmpty(this.errors)) {
      $('.done').show();
      return Promise.resolve();
    }

    $('.done').hide();
    alert('В файле есть ошибки');
    return Promise.reject();
  }

  /**
   * Добавление работников в БД
   * @param opt
   * @returns {Promise}
   * @private
   */
  _addWorkers (opt) {
    return new Promise((resolve, reject) => {
      if(opt.errors && opt.errors.length > 0)
        return reject('file has errors');

      console.log(opt.edrpou);
    });
  }

  /**
   * И тут начинается пиздорез с проверкой
   * @param edrpou
   * @private
   */
  _checkFile (edrpou) {
    return new Promise((resolve, reject) => {
      let organization = this._parseFirstLine(this.lines[0]);
      organization.edrpou = edrpou;

      // получаем сотрудников
      let workers = {main: {workers: []}},
        currentCategory = null,
        currentSubCategory = null,
        matches;

      for(let i = 1; i < this.lines.length; i++) {
        const line = this.lines[i].trim();
        if(line.length == 0) continue;

        // проверяем на неразрещенные символы
        matches = this.CHECK_SYMBOLS.exec(line);
        if(matches) {
          this.addError('Неразрещенный символ', i, matches.index);
        }

        // =категория
        matches = /^=([^=]+)$/.exec(line);
        if(matches) {
          if(currentCategory)
            this.addError('Открытие категории, но предыдущая еще не закрылась', i);
          currentCategory = matches[1];
          continue;
        }

        // закрытие категории "=="
        if(/^\s*==\s*$/.test(line)) {
          if(!currentCategory)
            this.addError('Закрытие категории, но открытия не было', i);
          currentCategory = null;
          continue;
        }

        // -подкатегория
        matches = /^-([^-].*)$/.exec(line);
        if(matches) {
          if(currentSubCategory)
            this.addError('Открытие подкатегории, но предыдущая еще не закрылась', i);
          if(!currentCategory)
            this.addError('Открытие подкатегории, но родительская категория не была найдена', i);
          currentSubCategory = matches[1];
          continue;
        }

        // закрытие подкатегории "--"
        if(/^\s*--\s*$/.test(line)) {
          if(!currentSubCategory)
            this.addError('Закрытие подкатегории, но открытия не было', i);
          currentSubCategory = null;
          continue;
        }

        // парсим работников
        const cat = currentCategory ? currentCategory : 'main';
        if(!workers[cat]) workers[cat] = {
          workers: [],
          sub: {}
        };
        let curCategoryWorkers;

        if(currentSubCategory) {
          if(!workers[cat].sub[currentSubCategory])
            workers[cat].sub[currentSubCategory] = [];
          curCategoryWorkers = workers[cat].sub[currentSubCategory];
        }
        else curCategoryWorkers = workers[cat].workers;

        this._parseWorker(line, {
          workers: curCategoryWorkers,
          line: i
        });
      }

      if (currentCategory)
        this.addError('Неверно открыты/закрыты категории.', this.lines.length);

      // проверяем работников по БД
      this._checkWorkers(workers).then(function(fioErrors) {
        resolve({fioErrors, workers, organization});
      }.bind(this));
    });
  }

  /**
   *
   * @param value
   * @returns {Object}
   * @private
   */
  _parseFirstLine (value) {
    let data = value.split(','),
      date = data[0].trim(),
      workersCount = data[1].trim();

    // проверяем дату
    if(!/^\d{2}\.\d{2}\.\d{4}$/.test(date))
      this.addError(`Неверный формат даты "${date}" (должен быть <11.11.2222>)`, 0);

    // кол-во сотрудников
    if(!/^(\d+|-)$/.test(workersCount))
      this.addError(`Кол-во сотрудников указано неверно "${workersCount}" (должно быть число либо <->)`, 0);

    return {
      workersCount: parseInt(data[1].trim()),
      text: data.slice(2).join(',').trim(),
      date
    };
  }

  /**
   *
   * @param value
   * @param opt
   * @private
   */
  _parseWorker (value, opt) {
    let matches = /([^,]+),(.*)$/.exec(value);
    if(!matches) {
      this.addError('Невозможно распознать ФИО и должность', opt.line);
      return;
    }

    const position = matches[2].trim();
    if (!this.CHECK_LETTERS.test(position)) {
      this.addError('Невозможно распознать ФИО и должность', opt.line);
      return;
    }

    const fio = matches[1].trim();
    matches = this.CHECK_FIO.exec(fio);
    if(matches) {
      this.addError('Неразрещенный символ', opt.line, matches.index);
      return;
    }

    opt.workers.push({fio, position});
  }

  /**
   * Проверяем ФИО по БД
   * @param workers
   * @returns {*}
   * @private
   */
  _checkWorkers (workers) {
    // отправляем на сервер только массив ФИО
    var data = [];
    for(let i in workers) {
      for (let worker of workers[i].workers)
        data.push(worker.fio);
      for (let j in workers[i].sub)
        for (let worker of workers[i].sub[j])
          data.push(worker.fio);
    }

    return $.ajax({
      url: '/admin/api/worker/check_new_workers',
      method: 'post',
      data: {
        workers: JSON.stringify(data)
      }
    });
  }

  /**
   * Выводим файл и ошибки проверки файла
   * @private
   */
  _showFile () {
    const $el = $('.file');
    $el.html('');

    // выводим построчно весь файл
    for(let i = 0; i < this.lines.length; i++) {
      let line = this.lines[i], lineErrors;

      // ошибки в этой строке
      if(this.errors[i]) {
        lineErrors = this.errors[i].map((err) => {
          // если есть позиция символа, выделяем его
          if(err[1]) {
            var s = line.substr(err[1], 1);
            line = line.substr(0, err[1]) + `<span class="error-sym">${s}</span>` + line.substr(err[1] + 1);
          }
          return err[0] + '<br>';
        });
      }

      // если в строке работник и по нему есть ошибки, выводим их
      if(i != 0 && !/^(--|==)/.test(line) && line.length != 0) {
        let matches = /([^,]+),/.exec(line);
        if(matches && this.fioErrors.indexOf(matches[1]) >= 0) {
          line = `<span class="warning-sym">${matches[1]}</span>` + line.substr(matches[1].length);
        }
      }

      $el.append(line + '<br>');
      if(lineErrors)
        $el.append(`<p class="alert alert-danger">${lineErrors}</p>`);
    }

    return Promise.resolve();
  }
}

export default new Controller();
export default new class
{
  index(orgnaization, snapshot) {
    $('.worker .name').click(this.editWorker.bind(this));
    $('.department-name').click(this.editDepartment.bind(this, snapshot));
  }


  /**
   * Редактирование сотрудника
   * @param e
   * @returns {*}
   */
  editWorker(e) {
    const $el = $(e.currentTarget),
      source = $el.text().split(','),
      data = {};

    // спрашиваем ФИО
    let answer;
    if(_.isEmpty(answer = prompt('ФИО', source[0])))
      return;
    data.fio = answer.trim();

    // спрашиваем должность
    if(_.isEmpty(answer = prompt('должность', source[1].trim())))
      return;
    data.position = answer.trim();

    return $.ajax({
      method: 'post',
      url: `/admin/api/worker/${$el.data('id')}`,
      data,
      success: (data) => $el.html(`${data.fio}, ${data.position}`)
    });
  }

  /**
   * Редактирование название отдела
   * @param snapshot
   * @param e
   */
  editDepartment(snapshot, e) {
    const $el = $(e.currentTarget),
      name = $el.text();
    let result = prompt('Отдел', name);
    if(_.isEmpty(result)) return;

    $.ajax({
      method: 'post',
      url: '/admin/api/worker/change_department',
      data: {
        snapshot, name,
        newName: result.trim(),
        field: $el.data('sub') ? 'subDepartment' : 'department'
      },
      success: (data) => $el.html(result)
    });
  }
}
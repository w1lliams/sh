<?php

namespace App\Http\Controllers\Admin;

use App\Fio;
use App\Http\Controllers\Controller;
use App\Note;
use App\Organization;
use App\Snapshot;
use App\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;

class WorkerController extends Controller
{
  /**
   * Страница с сотрудниками
   * @param Organization $organization
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function addWorkersPage(Organization $organization)
  {
    return view('admin.organization.add_workers', [
      'organization' => $organization,
      'menu' => 'add_workers'
    ]);
  }

  /**
   * Выводим список снимков списка сотрудников (снимок - 1 загрузка всех сотрудников компании)
   * @param Organization $organization
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function workersPage(Organization $organization)
  {
    $snapshots = Snapshot::where('organization_id', $organization->id)->get();

    return view('admin.organization.snapshots', [
      'organization' => $organization,
      'snapshots' => $snapshots,
      'menu' => 'workers'
    ]);
  }

  /**
   * Список работников
   * @param Organization $organization
   * @param Snapshot $snapshot
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function snapshotPage(Organization $organization, Snapshot $snapshot)
  {
    $workers = Worker::where('snapshot_id', $snapshot->id)->get();
    $workers = Worker::structure($workers);

    return view('admin.organization.workers', [
      'organization' => $organization,
      'snapshot' => $snapshot,
      'workers' => $workers,
      'menu' => 'workers'
    ]);
  }

  /**
   * Страница с заметками к работнику (машины, недвижимость)
   * @param Worker $worker
   * @return mixed
   */
  public function workerNotesPage(Worker $worker)
  {
    return view('admin.organization.worker_notes', [
      'worker' => $worker
    ]);
  }

  /**
   * Добалвение заметки к работнику
   * @param Request $request
   * @param Worker $worker
   * @return mixed
   */
  public function addNote(Request $request, Worker $worker)
  {
    $this->validate($request, [
      'text' => 'required',
      'type' => 'required',
      'url'  => 'url'
    ]);

    $worker->notes()->create($request->all());
    return redirect()->route('admin::worker_notes', $worker->id);
  }

  /**
   * @param Note $note
   * @return \Illuminate\Http\RedirectResponse
   * @throws \Exception
   */
  public function deleteNote(Note $note)
  {
    $note->delete();
    return redirect()->route('admin::worker_notes', $note->worker_id);
  }

  /**
   * Проверяем ФИО работников на валидность
   * @return \Illuminate\Http\JsonResponse
   */
  public function checkNewWorkers()
  {
    $result = [];
    $workers = json_decode(Input::get('workers'), true);

    foreach(array_chunk($workers, 1000) as $chunk) {
      Fio::checkFIO($chunk, $result);
    }

    return response()->json($result);
  }

  /**
   * Добавляем в БД всех сотрудников организации
   *
   * @param Request $request
   * @param Organization $organization
   */
  public function pushWorkers(Request $request, Organization $organization)
  {
    $workers = [];

    // создаем метку, которая привязывается ко всем сотрудникам
    // и сохраняем название организации и едрпоу, на тот случай если они потом изменяться
    $snapshot = new Snapshot;
    $snapshot->name = $organization->fullName;
    $snapshot->edrpou = $organization->edrpou;
    $snapshot->date = Carbon::parse($request->get('date'));
    $snapshot->organization()->associate($organization);
    $snapshot->save();

//    $organization->snapshot()->associate($snapshot);
//    $organization->save();

    // разделы
    foreach ($request->get('workers') as $departmentName => $departmentData) {
      if($departmentName == 'main')
        $departmentName = '';

      if(isset($departmentData['workers'])) {
        foreach ($departmentData['workers'] as $workerData) {
          $this->_createWorker($workers, $workerData, $snapshot, $organization, $departmentName);
        }
      }

      // подразделы
      if(isset($departmentData['sub'])) {
        foreach ($departmentData['sub'] as $subDepartmentName => $workersData) {
          foreach ($workersData as $workerData)
            $this->_createWorker($workers, $workerData, $snapshot, $organization, $departmentName, $subDepartmentName);
        }
      }
    }

    $snapshot->count = count($workers);
    $snapshot->save();
    $lastWorker = Worker::orderBy('id', 'desc')->first();
    Worker::insert($workers);

    // индексуруем работников
    $newWorkers = Worker::where('id', '>', $lastWorker->id)->get();
    $newWorkers->addToIndex();
  }

  /**
   * Редактирование работника
   * @param Request $request
   * @param Worker $worker
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, Worker $worker)
  {
    $worker->update($request->all());
    return response()->json($worker);
  }

  /**
   * Редактирование названия отдела/подотдела
   * @param Request $request
   */
  public function updateDepartment(Request $request)
  {
    $this->validate($request, [
      'snapshot' => 'required|numeric',
      'field' => 'in:department,subDepartment',
      'name' => 'required',
      'newName' => 'required'
    ]);

    $field = $request->get('field');
    Worker::where('snapshot_id', $request->get('snapshot'))
      ->where($field, $request->get('name'))
      ->update([$field => $request->get('newName')]);
  }

  /**
   * Добавляем сотрудника в БД
   *
   * @param array $result
   * @param array $workerData
   * @param Snapshot $snapshot
   * @param Organization $organization
   * @param string $department
   * @param string $subDepartment
   * @return bool
   */
  private function _createWorker(array &$result, array $workerData, Snapshot $snapshot, Organization $organization, $department = '', $subDepartment = '')
  {
    $result[] = [
      'fio'             => $workerData['fio'],
      'position'        => $workerData['position'],
      'department'      => $department,
      'subDepartment'   => $subDepartment,
      'snapshot_id'     => $snapshot->id,
      'organization_id' => $organization->id
    ];
  }
}

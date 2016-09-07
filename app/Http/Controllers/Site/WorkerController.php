<?php

namespace App\Http\Controllers\Site;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Worker;

class WorkerController extends Controller
{
    /**
     * @param Worker $worker
     * @return mixed
     */
    public function workerPage(Worker $worker)
    {
        $this->getCounters();
        return view('site.worker', [
          'worker' => $worker
        ]);
    }


  /**
   * ѕри получении уведомлени€ от нотификатор √иперкоммента, - обращаемс€ к ним по API и получаем количество отзывов дл€ сотрудника и плюсуем его в базе к notes_count
   * @param Request $request
   */
    public function getcounthypercomments(Request $request)
    {

	 // засовываем всЄ что пришло от сервера-нотификатора HyperComments в массив
	 $hcData=json_decode($request['data'],true);

	 $hcSecretkey = 'df28fhdjDJn3ujy7bsdhga73g63k6egx';
	 if($request['signature']<>md5($hcSecretkey.$request['data'].$request['time'])) exit('Error: invalid data'); // провер€ем, по ключу а запрос ли это от hypercomment-ов 

	 $worker_id = basename($hcData[0]['link']);

	 // обращаемс€ по API и получаем количество –ќƒ»“≈Ћ№— »’ отзывов (cm2) дл€ этой страницы. ќтветы на отзывы (cm2) не учитываем, т.к. работает достаточно глючно
	 if( $curl = curl_init() ) {
	    curl_setopt($curl, CURLOPT_URL, 'http://c1api.hypercomments.com/1.0/streams/get');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    $body = '{"widget_id":79747, "link":"none", "xid":"'.$hcData[0]['xid'].'"}';
	    curl_setopt($curl, CURLOPT_POSTFIELDS, 'body='.$body.'&signature='.sha1($body.$hcSecretkey));
	    $out = json_decode(curl_exec($curl),1);
	    curl_close($curl);
	  }

//	file_put_contents("/var/www/sh/storage/logs/laravel.log",$out['result'],FILE_APPEND); if  ($out['result']=="error") file_put_contents("/var/www/sh/storage/logs/laravel.log",$out['description'],FILE_APPEND);

	if ($out&&$out['result'] == 'success') {
		$additional_count=$out['data'][0]['cm'];
		$worker = Worker::find($worker_id);
		$worker->notes_count = count($worker->notes)+$additional_count;
		$worker->save();
		$worker->addToIndex();
		echo 'ok';
	} else {echo 'some error';}
	exit;

    }

}

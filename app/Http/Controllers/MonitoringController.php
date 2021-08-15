<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $FCM_AUTH_KEY = 'AAAAkT69ytM:APA91bFe2ewz1AmPqf0EIsqlwQ09dqZ66QTQ-bPHzM4jPpF0iZzZ0DqJ9jYaPdqM8qXZ15wZeoubTLtWjeLv3Z4fBphQDkQfUcFQ1y2Ai68ukq_HFk5eCKn84wXkwkaEZJWZsgs0xdtT'; 
    private $maxLimitTemperature = 30;
    private $maxLimitHumidity = 80;
    private $botID = '1626804461:AAEWZlXpGIsg2jIUQfB-uU9ik2VQ0Y1sI5w';
    private $chatTelegramID = '-419262691'; // Grup AMAN Info

    public function index()
    {

        $paginate = request('paginate') ? request('paginate') : null;

        if (request('order_by') == 'desc') {
            $status = 'success';
            $dataMonitoring = Monitoring::orderBy('created_at', 'DESC')->paginate($paginate);
            $responseCode = Response::HTTP_OK;
        } else if (request('order_by') == 'asc') {
            $status = 'success';
            $dataMonitoring = Monitoring::orderBy('created_at', 'ASC')->paginate($paginate);
            $responseCode = Response::HTTP_OK;
        } else {
            $status = 'fail';
            $dataMonitoring = "Cannot return data, parameter is wrong";
            $responseCode = Response::HTTP_NOT_FOUND;
        }
        
        $response = [
            'status' => $status,
            'data' => $dataMonitoring
        ];

        return response()->json($response, $responseCode); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function latest()
    {
        $response = [
            'status' => 'success',
            'data' => Monitoring::orderBy('created_at', 'DESC')->first(),
        ];

        return response()->json($response, Response::HTTP_OK); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function storeWithGet()
     {

        date_default_timezone_set("Asia/Jakarta");

        $temperature = request('temperature');
        $humidity = request('humidity');

        $temperature = Monitoring::create([
            'temperature'   => $temperature,
            'humidity'      => $humidity,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ]);

        $getDevices = Device::get();

        if ($temperature->temperature >= $this->maxLimitTemperature) {
            foreach ($getDevices as $d) {
                $this->_sendNotif($d->token, 'Suhu terlalu tinggi', 'Suhu DVOR terlalu tinggi! Lakukan tindakan sekarang', '', '');
            }
            $this->_sendTelegramChat("Suhu DVOR terlalu tinggi, ambil tindakan sekarang");
        }


        if ($temperature->humidity >= $this->maxLimitHumidity) {
            foreach ($getDevices as $d) {
                $this->_sendNotif($d->token, 'Ruangan terlalu lembab', 'Ruangan DVOR terlalu lembab! Lakukan tindakan sekarang', '', '');
            }
            $this->_sendTelegramChat("Kelembapan DVOR terlalu tinggi, ambil tindakan sekarang");
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Data has been added with GET',
                'data' => [
                    "temperature" => $temperature->temperature,
                    "humidity" => $temperature->humidity,
                    "updated_at" => $temperature->created_at,
                    "data_order" => $temperature->id
                ]
        ], Response::HTTP_CREATED);

     }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),
            Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $temperature = Monitoring::create($request->all());
            $response = [
                'status' => 'success',
                'message' => 'Data has been added',
                'data' => [
                    "temperature" => $temperature->temperature,
                    "humidity" => $temperature->humidity,
                    "updated_at" => $temperature->created_at,
                    "data_order" => $temperature->id
                ]
            ];

            $getDevices = Device::get();

            if ($temperature->temperature >= $this->maxLimitTemperature) {
                foreach ($getDevices as $d) {
                    $this->_sendNotif($d->token, 'Suhu terlalu tinggi', 'Suhu DVOR terlalu tinggi! Lakukan tindakan sekarang', '', '');
                }
                $this->_sendTelegramChat("Kelembapan DVOR terlalu tinggi, ambil tindakan sekarang");
            }

            if ($temperature->humidity >= $this->maxLimitHumidity) {
                foreach ($getDevices as $d) {
                    $this->_sendNotif($d->token, 'Ruangan terlalu lembab', 'Ruangan DVOR terlalu lembab! Lakukan tindakan sekarang', '', '');
                }
                $this->_sendTelegramChat("Kelembapan DVOR terlalu tinggi, ambil tindakan sekarang");
            }


            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed, ' . $e->errorInfo
            ]);
        }
    }



    private function _sendNotif($to, $title, $body, $icon, $url) {
        $postdata = json_encode(
            [
                'notification' => 
                    [
                        'title' => $title,
                        'body' => $body,
                        'icon' => $icon,
                        'click_action' => $url
                    ]
                ,
                'to' => $to
            ]
        );
    
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json'."\r\n"
                            .'Authorization: key='. $this->FCM_AUTH_KEY ."\r\n",
                'content' => $postdata
            )
        );
    
        $context  = stream_context_create($opts);
    
        $result = file_get_contents('https://fcm.googleapis.com/fcm/send', false, $context);
        if($result) {
            return json_decode($result);
        } else return false;
    }

    private function _sendTelegramChat($message_text) {

        $url = "https://api.telegram.org/bot" . $this->botID . "/sendMessage?parse_mode=markdown&chat_id=" . $this->chatTelegramID;
        $url = $url . "&text=" . urlencode($message_text);
        $ch = curl_init();
        $optArray = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);

        return false;
    }

    public function config()
    {
        $response = [
            'status'    => true,
            'data'      => [
                'maxLimitTemperature'   => $this->maxLimitTemperature,
                'maxLimitHumidity'      => $this->maxLimitHumidity,
            ]
        ];

        return response()->json($response, Response::HTTP_OK); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

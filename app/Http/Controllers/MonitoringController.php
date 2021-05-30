<?php

namespace App\Http\Controllers;

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

            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed, ' . $e->errorInfo
            ]);
        }
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

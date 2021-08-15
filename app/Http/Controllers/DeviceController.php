<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'name'  => ['required'],
            'email' => ['required'],
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),
            Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $checkIsDeviceExist = Device::where('email', $request->email)->first();

        if (empty($checkIsDeviceExist)) {
            try {
                $device = Device::create($request->all());
                $response = [
                    'status' => 'success',
                    'message' => 'Device has been added',
                    'data' => [
                        "name"  => $device->name,
                        "email" => $device->email,
                        "token" => $device->token,
                        "device_info" => $device->device_info
                    ]
                ];
    
                return response()->json($response, Response::HTTP_CREATED);
            } catch (QueryException $e) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Failed, ' . $e->errorInfo
                ]);
            }
        } else {

            try {
                $device = Device::where('email', $request->email)->update(['name' => $request->name, 'token' => $request->token]);
                $response = [
                    'status' => 'success',
                    'message' => 'Device has been updated token',
                    'data' => [
                        "name"  => $device->name,
                        "email" => $device->email,
                        "token" => $device->token,
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

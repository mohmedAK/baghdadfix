<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "to_phone" => "required",
            "message" => "required",
            "status" => "required",
            "request_id" => "required"
        ], [
            "to_phone.required" => "to_phone is required",
            "message.required" => "message is required",
            "status.required" => "status is required",
            "request_id.required" => "request_id is required"

        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        $otp = new Otp();
        $otp->to_phone = $request->to_phone;
        $otp->message = $request->message;
        $otp->status = $request->status;
        $otp->request_id = $request->request_id;

        $result = $otp->save();
        if ($result) {
            return ["Result" => "Data has been saved"];
        } else {
            return ["Result" => "Operation Failed"];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        if ($request->id) {
            $result =  Otp::find($request->id);
            return $result ? response($result, 200) : response(["Result" => "No Data Found"], 201);
        } else {
            $result = Otp::get();
            return sizeof($result) > 0 ? response($result, 200) : response(["Result" => "No Data Found"], 201);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Otp $favorite)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,)
    {
        $otp = Otp::Find($request->id);
        $otp->to_phone = $request->to_phone;
        $otp->message = $request->message;
        $otp->status = $request->status;
        $otp->request_id = $request->request_id;

        $result = $otp->save();
        if ($result) {
            return response(["Result" => "Data has been updated"], 200);
        } else {
            return response(["Result" => "Operation Failed"], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response(["Result" => "id is required"], 201);
        }



        $result = Otp::where('id', $request->id)->delete();

        if ($result) {
            return response(["Result" => "Data has been deleted"], 200);
        } else {
            return response(["Result" => "id not found"], 201);
        }
    }
}

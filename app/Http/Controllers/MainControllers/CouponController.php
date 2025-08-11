<?php
namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
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
    public function create (Request $request)
    {
        // dd($request->all());
          $request->validate([
            'copon_text' => 'required|unique:copons',
            'discount' => 'required',

        ],[
            'copon_text.required' => 'كود الخصم مطلوب',
            'copon_text.unique' => 'هذا الكود موجود بالفعل',
            'discount.required' => 'قيمة الخصم مطلوبة',
        ]);
        //dd($request->all());
        $copon = new Coupon();
        $copon->copon_text = $request->copon_text;
        $copon->discount = $request->discount;
        $copon->save();


        return back()
            ->with('success','لقد قمت بإضافة  قسيمة الخصم بنجاح.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'copon_text' => 'required|unique:copons',
            'discount' => 'required',
        ], [
            'copon_text.required' => 'كود الخصم مطلوب',
            'copon_text.unique' => 'هذا الكود موجود بالفعل',
            'discount.required' => 'قيمة الخصم مطلوبة',
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }

        $copon = new Coupon;
        $copon->copon_text = $request->copon_text;
        $copon->discount = $request->discount;
        $result = $copon->save();

        if($result){
            return ["Result"=>"Data has been saved"];
        }else{
            return ["Result"=>"Operation Failed"];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        if ($request->id) {
            $result =  Coupon::find($request->id);
            return $result ? response($result, 200) : response(["Result" => "No Data Found"], 201);
        } else {

            $result = Coupon::all();
            return sizeof($result) > 0 ? response($result, 200) : response(["Result" => "No Data Found"], 201);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $copon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $copon)
    {
        $copon = Coupon::Find($request->id);
        $copon->copon_text = $request->copon_text;
        $copon->discount = $request->discount;

        $result = $copon->save();
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
        $result = Coupon::where('id', $request->id)->delete();

        if ($result) {
            return back()
            ->with('success','لقد تم حذف قسيمة الخصم بنجاح.');
        } else {
            return back()
            ->with('error','لم يتم حذف  قسيمة الخصم .');
        }
    }

}

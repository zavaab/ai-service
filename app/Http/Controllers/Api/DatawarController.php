<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class DatawarController extends Controller
{
    
    
    public function calltorout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_key' => 'required|numeric',
            'shop_key' => 'required|numeric'
             ]);
            if (!$validator->fails())
            {
                $user_key = $request->user_key;
                $shop_key = $request->shop_key;
        
        
                try {
                    $res = DB::connection('sqlsrv')->select('SELECT * FROM [dbo].[CallToRout](?,?)', [$user_key, $shop_key])[0];
                    return response()->json(
                        [
                        'result' => $res,
                     ], 200);
                } catch (\Exception $e) {
                    dd($e->getMessage()); // Output the error message for debugging
                }

            } 
           else{
            return $validator->errors();
           }
            
            }
 


}

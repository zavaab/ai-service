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
                    $res = DB::connection('sqlsrv')->select('SELECT * FROM [dbo].[CallToRout](?,?)', [$user_key, $shop_key]);

if (!empty($res)) {
    // Data exists
    $data = $res[0];
return response()->json(
                        [
                        'result' => $res[0],
                     ], 200);

    // You can now use $data as needed
} else {
 return response()->json(
                        [
                        'result' => ['CallRout' => '0' , 'CntVis' => '0' , 'CntRt' => '0' ],
                     ], 200);

    // Data does not exist
    // Handle the case when no data is found
}

                   //return response()->json(
                     //   [
                       // 'result' => $res,
                     //], 200);
                } catch (\Exception $e) {
                    dd($e->getMessage()); // Output the error message for debugging
                }

            } 
           else{
            return $validator->errors();
           }
            
            }
 


}

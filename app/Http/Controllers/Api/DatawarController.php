<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class DatawarController extends Controller
{
    
    
    public function calltorout(Request $request)
    {
         
        $request->validate([
            'user_key' => 'required|numeric',
            'shop_key' => 'required|numeric'
        ]);
        $user_key = $request->user_key;
        $shop_key = $request->shop_key;
        $results = DB::select( DB::raw("SELECT * FROM [dbo].[CallToRout] (".$user_key.",".$shop_key.")") );
        // $resultArray = json_decode(json_encode($results), true);
        return response()->json(
            [
            'result' => $results,
         ], 200);

        

        
    }


    
     


}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VisitsAi;
use App\Models\VisitsAiResult;
use App\Services\ApiService;

use Illuminate\Support\Facades\DB;



class AiController extends Controller
{
    
    
    public function store(Request $request)
    {
         

        $request->validate([
            'visitId' => 'required|numeric',
            'categories' => 'required|array',
            'categories.*.categoryId' => 'required|numeric',
            'categories.*.images' => 'required|array',
            'categories.*.images.*.name' => 'required|string',
            'categories.*.images.*.url' => 'required|url',
        ]);

        $visitId = $request->visitId;
        $error = true ;
        // Loop through categories and images
        
        
        $exists = VisitsAi::where('visit_id', $visitId)->exists();
        if ($exists) {

            return $this->result($request);
             
        }
 
        foreach ($request->categories as $categoryData) {
           
            foreach ($categoryData['images'] as $image) {
                $apiService = new ApiService();
                $params['image_url'] = $image['url'];
                $params['category'] = $categoryData['categoryId'];
                $res = $apiService->sendApiRequest($params);
                
                if( isset($res['id']) && (isset($res['status']) && $res['status'] == 200 )  ){
                    $visit = VisitsAi::updateOrCreate(
                        [
                            'visit_id' => $visitId,
                            'category_id' => $categoryData['categoryId'],
                            'ai_id' => $res['id'],
                            'name' => $image['name'],
                            'url' => $image['url'],
                            'status' => 'pending',
                        ]
                    );
 
                $error = false;
                }else{
                    return response()->json(['result' =>  $res['result'] , 'code' => 500 ], 500);  
                }
                
            }
            
        }

        if(!$error)
              return response()->json(['result' => 'Data submitted successfully' , 'code' => 200 ], 200);  

        
    }


    
     /**
    * Handle an incoming ai request.
    */
    public function result(Request $request)
    {
 
        
         
        $request->validate([
            'visitId' => 'required|numeric',
        ]);

        $results = DB::table('visits_ai')
            ->where('visits_ai.visit_id', '=', $request->visitId)
            ->where('status','pending')
            ->select(
                'visits_ai.id',
                'visits_ai.ai_id',
                'visits_ai.visit_id',
                'visits_ai.category_id',
                'visits_ai.name',
                'visits_ai.url',
            )
            ->get();
            // dd($results);
             
            $apiService = new ApiService();
            foreach ($results as $result) {
            
                $res = $apiService->sendApiResult( $result->ai_id );
                 
                
                if(isset($res['detections'])){
                    
                        $status = $res['detections']['status'];
                        VisitsAi::where('ai_id' , $result->ai_id )
                        ->update([
                            'status' => $status,
                        ]);

                    foreach ($res['detections'] as $key => $value) {
                         
                        if( $key != 'id' && $key != 'status' ){
                            VisitsAiResult::Create(
                                [
                                    'code' => $key,
                                    'count' => $value,
                                    'visits_ai_id' => $result->id
                                ]
                            );
                        }
                    }
                }else if(isset($res['code'])){
                    VisitsAi::where('ai_id' , $result->ai_id )
                    ->update([
                        'status' => $res['result'],
                    ]);
                }
               
                
            }


            // $res = DB::select('SELECT va.visit_id, va.status, va.category_id, var.`code`,var.count,h.product_id, h.brand,h.category, h.`name`, h.`name_fa` 
            // FROM visits_ai va LEFT JOIN visits_ai_results var ON va.id = var.visits_ai_id  LEFT JOIN henkel h ON var.`code` COLLATE utf8mb4_unicode_ci = h.`code` 
            // WHERE va.visit_id = '.$request->visitId.' COLLATE utf8mb4_unicode_ci;');
            
            
            $status_visit_id = VisitsAi::WHERE('status' , 'pending')->where('visit_id',$request->visitId)->count();
 
            $res = DB::select('SELECT 
            h.product_id ,
            va.category_id,
            var.count,
            var.`code`,
            va.status 
            FROM visits_ai va LEFT JOIN visits_ai_results var ON va.id = var.visits_ai_id  LEFT JOIN henkel h ON var.`code` COLLATE utf8mb4_unicode_ci = h.`code` 
            WHERE va.visit_id = '.$request->visitId.' COLLATE utf8mb4_unicode_ci;');
            return response()->json(
                [
                'code' => 200,
                'status' => ($status_visit_id != 0 ) ?  'pending' : 'Done',
                'visit_id' => $request->visitId,
                'products' => $res
             ], 200);


    }


}

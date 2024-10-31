<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VisitsAi;
use App\Models\VisitsAiResult;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;



class AiController extends Controller
{
    
    
    public function store(Request $request)
    {
         
  
           $validatedData = $request->validate([
        'visitId' => 'required|numeric',
        'categories' => 'required|array',
        'categories.*.categoryId' => 'required|numeric',
        'categories.*.images' => 'required|array',
        'categories.*.images.*.name' => 'required|string',
        'categories.*.images.*.url' => 'required|url',
    ]);

    // Log the validated request data
    Log::info('Request validated successfully', [
        'visitId' => $validatedData['visitId'],
        'categories' => $validatedData['categories']
    ]);


        //$request->validate([
         //   'visitId' => 'required|numeric',
           // 'categories' => 'required|array',
            //'categories.*.categoryId' => 'required|numeric',
            //'categories.*.images' => 'required|array',
            //'categories.*.images.*.name' => 'required|string',
            //'categories.*.images.*.url' => 'required|url',
       // ]);

       // Log::info('Request after validated successfully', [
       // 'visitId' => $validatedData['visitId'],
        //'categories' => $validatedData['categories']
        // ]);

        $visitId = $request->visitId;
        $error = true ;
        // Loop through categories and images
        
       $cat_map = [
        1 => 5,
        2 => 6,
        3 => 7,
        4 => 9,
        5 => 4,
        6 => 12,
        7 => 8,
        8 => 2,
        9 => 1,
        10 => 10,
        11 => 3,
        12 => 13,
      ];

        $exists = VisitsAi::where('visit_id', $visitId)->exists();
        if ($exists) {

            return $this->result($request);
             
        }


  

 
        foreach ($request->categories as $categoryData) {
           
            foreach ($categoryData['images'] as $image) {
                $apiService = new ApiService();
                $params['image_url'] = $image['url'];
                $params['category'] = array_key_exists($categoryData['categoryId'], $cat_map) ? $cat_map[$categoryData['categoryId']] : $categoryData['categoryId']; //$categoryData['categoryId'];
                $res = $apiService->sendApiRequest($params);
                
                if( isset( $res['id']) && (isset($res['status']) && $res['status'] == "616" )  ){

                     Log::info('ai_id received successfully from ai', [
                         'visitId' => $visitId,
                         'ai_id' => $res['id'],
			 'status' => $res['status']
                         ]);
                   
                    $visit = VisitsAi::updateOrCreate(
                        [
                            'visit_id' => $visitId,
                            'category_id' => $categoryData['categoryId'],
			    'category_id_map' => array_key_exists($categoryData['categoryId'], $cat_map) ? $cat_map[$categoryData['categoryId']] : $categoryData['categoryId'],
                            'ai_id' => $res['id'],
                            'name' => $image['name'],
                            'url' => $image['url'],
                            'status' => $res['status'], //for example : 616 success
                        ]
                    );
 
              
                }else if( isset($res['status']) ){
		     Log::info('ai_id not received from ai', [
                         'visitId' => $visitId,
                         'ai_id' => '',
                         'status' => $res['status']
                         ]);
                    $visit = VisitsAi::updateOrCreate(
                        [
                            'visit_id' => $visitId,
                            'category_id' => $categoryData['categoryId'],
                            'category_id_map' => array_key_exists($categoryData['categoryId'], $cat_map) ? $cat_map[$categoryData['categoryId']] : $categoryData['categoryId'],
                            'ai_id' => '0' ,
                            'name' => $image['name'],
                            'url' => $image['url'],
                            'status' => $res['status'], //for example : 601 = Cant get image from inserted url
                        ]
                    );
                
                }
                else{
                    VisitsAi::where('visit_id', $visitId)->delete();
                    // return response()->json(['result' =>  'UnknowError' , 'code' => 404 ], 404); 
                    
                    return response()->json(
                        [
                        'status' => 'unknowError',
                        'visit_id' => $request->visitId,
                        'msg' => 'Unknow Error, Deleted VisitId: '.$visitId,
                     ], 200);

                }
                
            }
            
        }

        return response()->json(
            [
            'status' => 'Success',
            'visit_id' => $request->visitId,
            'msg' => 'Data submitted successfully',
         ], 200);

        return response()->json(['result' => 'Data submitted successfully' , 'code' => 616 ], 200);  

        
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
            ->where('visits_ai.visit_id', $request->visitId)
            ->where('status','616')
            ->select(
                'visits_ai.id',
                'visits_ai.ai_id',
                'visits_ai.visit_id',
                'visits_ai.category_id_map',
                'visits_ai.category_id',
                'visits_ai.name',
                'visits_ai.url',
            )
            ->get();
             
             
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
            
            
            $status_visit_id_detected = VisitsAi::where( 'status' , '612' )->where( 'visit_id' , $request->visitId )->count();
            $status_visit_id_failed = VisitsAi::where( 'status' , '<>' ,  '612' )->where( 'status' , '<>' ,  '616' )->where( 'visit_id' , $request->visitId )->count();
            $status_visit_id_pending = VisitsAi::where( 'status' ,  '616' )->where( 'visit_id' , $request->visitId )->count();
            $status_visit_id_all = VisitsAi::where( 'visit_id' , $request->visitId )->count();
           
 
            if($status_visit_id_failed == $status_visit_id_all){
                return response()->json(
                    [
                    'status' => 'Failed',
                    'visit_id' => $request->visitId,
                    'products' => [],
                    'msg' => ''
                 ], 200);
            }
            if($status_visit_id_pending != 0){
                return response()->json(
                    [
                    'status' => 'Pending',
                    'visit_id' => $request->visitId,
                    'products' => [],
                    'msg' => ''
                 ], 200);
            }

            // -- va.status,
            // -- err.description
            $res = DB::select('SELECT 
            h.product_id ,
            va.category_id,
            va.category_id_map,
            var.count,
            var.`code`
            FROM visits_ai va LEFT JOIN visits_ai_results var ON va.id = var.visits_ai_id  LEFT JOIN henkel h ON var.`code` COLLATE utf8mb4_unicode_ci = h.`code` 
            LEFT JOIN error_description err ON va.`status` = err.`error_number`
            WHERE va.visit_id = '.$request->visitId.' COLLATE utf8mb4_unicode_ci  AND h.product_id IS NOT NULL;');

            if( $status_visit_id_detected != 0 ){
                return response()->json(
                    [
                    'status' => 'Done',
                    'visit_id' => $request->visitId,
                    'products' => $res,
                    'msg' => ''
                 ], 200);         
            }

            return response()->json(
                [
                'status' => 'Unknown',
                'visit_id' => $request->visitId,
                'products' => [],
                'msg' => ''
             ], 200); 
            // return response()->json(
            //     [
            //     'status' => ($status_visit_id != 0 ) ?  'pending' : 'Done',
            //     'visit_id' => $request->visitId,
            //     'products' => $res
            //  ], 200);


    }


}

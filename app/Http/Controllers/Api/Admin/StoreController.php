<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Mail\MobilePasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use App\Models\Sale\Store as RecordModel ;
use App\Http\Controllers\CrudController;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class StoreController extends Controller
{
    private $selectFields = [
        'id',
        'name' ,
        'location_name' ,
        'lat_long',
        'address' ,
        'images',
        'phone'
    ];
    /**
     * Listing function
     */
    public function index(Request $request){
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 10 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;

        $queryString = [
            // "where" => [
            //     'default' => [
            //         [
            //             'field' => 'type_id' ,
            //             'value' => $type === false ? "" : $type
            //         ]
            //     ],
            //     'in' => [] ,
            //     'not' => [] ,
            //     'like' => [
            //         [
            //             'field' => 'number' ,
            //             'value' => $number === false ? "" : $number
            //         ],
            //         [
            //             'field' => 'year' ,
            //             'value' => $date === false ? "" : $date
            //         ]
            //     ] ,
            // ] ,
            // "pivots" => [
            //     $unit ?
            //     [
            //         "relationship" => 'units',
            //         "where" => [
            //             "in" => [
            //                 "field" => "id",
            //                 "value" => [$request->unit]
            //             ],
            //         // "not"=> [
            //         //     [
            //         //         "field" => 'fieldName' ,
            //         //         "value"=> 'value'
            //         //     ]
            //         // ],
            //         // "like"=>  [
            //         //     [
            //         //        "field"=> 'fieldName' ,
            //         //        "value"=> 'value'
            //         //     ]
            //         // ]
            //         ]
            //     ]
            //     : []
            // ],
            "pagination" => [
                'perPage' => $perPage,
                'page' => $page
            ],
            "search" => $search === false ? [] : [
                'value' => $search ,
                'fields' => [
                    'name', 'address', 'location_name', 'phone'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields , [
            'images' => function($record){
                return is_array( $record->images ) && !empty( $record->images ) ? array_map(function( $image ){ 
                    return \Storage::disk('public')->exists( $image ) ? \Storage::disk("public")->url( $image  ) : "" ;
                }, $record->images ) : [] ;
            }
        ] );

        // $crud->setRelationshipFunctions([
        //     'units' => ['id','name'] ,
        //     'stock' => ['id','attribute_id','unit_id','quantity']
        // ]);

        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at')
        // ->whereHas( 'roles' , function( $query ){
        //     $query->where('name','Client');
        // })
        ; 

        $responseData = $crud->pagination(true, $builder);
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Create an account
     */
    public function store(Request $request){
        $record = RecordModel::where('name',$request->name)
        ->where('location_name',$request->location_name)
        ->first() ;
        if( $record ){
            // អ្នកប្រើប្រាស់បានចុះឈ្មោះរួចរាល់ហើយ
            return response([
                    'record' => $record ,
                    'message' => 'ទិន្នន័យ '.$record->name.' មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ],
                403
            );
        }else{
            // អ្នកប្រើប្រាស់ មិនទាន់មាននៅឡើយទេ
            $record = RecordModel::create([
                'name' => $request->name,
                'location_name' => $request->location_name,
                'lat_long' => $request->lat_long ,
                'address' => $request->address,
                'phone' => $request->phone ,
                'images' => []
            ]);

            return response()->json([
                'record' => $record ,
                'message' => 'គណនីបង្កើតបានជោគជ័យ !'
            ], 200);

        }
    }
    /**
     * Create an account
     */
    public function update(Request $request){
        $record = isset( $request->id ) && $request->id > 0 ? RecordModel::find($request->id) : false ;
        if( $record && $record->update([
            'name' => $request->name ,
            'location_name' => $request->location_name ,
            'lat_long' => $request->lat_long ,
            'address' => $request->address ,
            'phone' => $request->phone
        ]) == true ){
            return response()->json([
                'record' => $record ,
                'message' => 'កែប្រែព័ត៌មានរួចរាល់ !' ,
                'ok' => true
            ], 200);
        }else{
            // អ្នកប្រើប្រាស់មិនមាន
            return response([
                'record' => null ,
                'message' => 'ព័ត៌មានដែលអ្នកចង់កែប្រែព័ត៌មាន មិនមានឡើយ។' ,
                'ok' => false
            ], 403);
        }
    }
    /**
     * Active function of the account
     */
    public function active(Request $request){
        $record = RecordModel::find($request->id) ;
        if( $record ){
            $record->active = $request->active ;
            $record->save();
            // User does exists
            return response([
                'record' => $record ,
                'ok' => true ,
                'message' => 'ព័ត៌មាន '.$record->name.' បានបើកដោយជោគជ័យ !' 
                ],
                200
            );
        }else{
            // User does not exists
            return response([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមទោស គណនីនេះមិនមានទេ !' 
                ],
                201
            );
        }
    }
    /**
     * Function delete an account
     */
    public function destroy(Request $request){
        $record = RecordModel::find($request->id) ;
        if( $record ){
            $record->active = 0 ;
            $record->deleted_at = \Carbon\Carbon::now() ;
            $record->save();
            // record does exists
            return response([
                'ok' => true ,
                'record' => $record ,
                'message' => 'ព័ត៌មាន '.$record->name.' បានលុបដោយជោគជ័យ !' ,
                'ok' => true 
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'ok' => false ,
                'record' => null ,
                'message' => 'សូមទោស ព័ត៌មាននេះមិនមានទេ !' ],
                201
            );
        }
    }
    public function read(Request $request){
        if( !isset( $request->id ) || $request->id < 0 ){
            return response()->json([
                'ok' => false ,
                'message' => 'សូមបញ្ជាក់អំពីលេខសម្គាល់ព័ត៌មាន។'
            ],422);
        }
        $record = RecordModel::find($request->id);
        if( $record == null ){
            return response()->json([
                'ok' => false ,
                'message' => 'ស្វែងរកព័ត៌មានមិនឃើញឡើយ។'
            ],403);
        }

        return response()->json([
            'record' => $record ,
            'ok' => true ,
            'message' => 'អានព័ត៌មានបានរួចរាល់។'
        ],200);
    }
    /**
     * Get all the user with role of client
     */
    public function compact(Request $request){
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 10 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;

        $queryString = [
            // "where" => [
            //     'default' => [
            //         [
            //             'field' => 'type_id' ,
            //             'value' => $type === false ? "" : $type
            //         ]
            //     ],
            //     'in' => [] ,
            //     'not' => [] ,
            //     'like' => [
            //         [
            //             'field' => 'number' ,
            //             'value' => $number === false ? "" : $number
            //         ],
            //         [
            //             'field' => 'year' ,
            //             'value' => $date === false ? "" : $date
            //         ]
            //     ] ,
            // ] ,
            // "pivots" => [
            //     $unit ?
            //     [
            //         "relationship" => 'units',
            //         "where" => [
            //             "in" => [
            //                 "field" => "id",
            //                 "value" => [$request->unit]
            //             ],
            //         // "not"=> [
            //         //     [
            //         //         "field" => 'fieldName' ,
            //         //         "value"=> 'value'
            //         //     ]
            //         // ],
            //         // "like"=>  [
            //         //     [
            //         //        "field"=> 'fieldName' ,
            //         //        "value"=> 'value'
            //         //     ]
            //         // ]
            //         ]
            //     ]
            //     : []
            // ],
            "pagination" => [
                'perPage' => $perPage,
                'page' => $page
            ],
            "search" => $search === false ? [] : [
                'value' => $search ,
                'fields' => [
                    'name', 'address', 'location_name', 'phone'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields);
        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at')
        // Retrive only user with role of client
        // ->whereHas( 'roles' , function( $query ){
        //     $query->where('name','Client');
        // })
        ; 

        $responseData['records'] = $builder->get();
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Upload
     */
    public function upload(Request $request){
        $record = RecordModel::find( $request->id );
        if( $record ){
            $images = $record->images ;
            foreach( $_FILES['files']['tmp_name'] AS $index => $file ){
                $uniqeName = \Storage::disk('public')->putFile( "stores/".$record->id, new File( $_FILES['files']['tmp_name'][$index] ) );
                $images[] = $uniqeName ;
            }
            $record->images = $images ;
            $record->save();
            $images = [] ;
            foreach( $record->images AS $index => $image ){
                $images[] = \Storage::disk('public')->exists( $image ) ? \Storage::disk("public")->url( $image  ) : [] ;
            }
            $record->images = $images ;
            return response([
                'record' => $record ,
                'images' => $images ,
                'ok' => true ,
                'message' => 'ជោគជ័យក្នុងការដាក់រូបភាព។'
            ],200);
        }else{
            return response([
                'ok' => false ,
                'message' => 'សូមបញ្ជាក់អំពីផលិតផលរបស់រូបភាពនេះ។'
            ],403);
        }
    }
    /**
     * Set feature picture
     */
    public function featurePicture(Request $request){
        $record = RecordModel::find($request->id);
        if( $record && is_array( $record->images ) && !empty( $record->images ) ){
            // Clear the selected index out of the array
            $filtered = array_filter( 
                $record->images , 
                function( $image , $index ) 
                use ( $request ) {
                    return $index != $request->index ;
                } ,
                ARRAY_FILTER_USE_BOTH
            );
            // Add the selected index into the first of the array to make it feature
            array_unshift( $filtered , $record->images[$request->index]);
            $record->images = $filtered ;
            $record->save();
            return response()->json([
                'record' => $record ,
                'ok' => true ,
                'message' => 'បានប្ដូរទីតាំងរូបភាពរួចរាល់។'
            ], 200);
        }else{
            return response([
                'record' => $record ,
                'ok' => false ,
                'id' => $request->input() ,
                'message' => 'មានបញ្ហាពេលកំណត់រូបភាពបឋម។'
                ], 403);
        }
    }
    /**
     * Remove Picture
     */
    public function removePicture(Request $request){
        $record = RecordModel::find($request->id);
        if( $record && is_array( $record->images ) && !empty( $record->images ) ){
            // Clear the selected index out of the array
            $filtered = array_filter( 
                $record->images , 
                function( $image , $index ) 
                use ( $request ) {
                    return $index !== $request->index ;
                } ,
                ARRAY_FILTER_USE_BOTH
            );
            $record->images = $filtered ;
            $record->save();
            return response()->json([
                'record' => $record ,
                'ok' => true ,
                'message' => 'បានប្ដូរទីតាំងរូបភាពរួចរាល់។'
            ], 200);
        }else{
            return response([
                'record' => $record ,
                'ok' => false ,
                'id' => $request->input() ,
                'message' => 'មានបញ្ហាពេលកំណត់រូបភាពបឋម។'
                ], 403);
        }
    }
}

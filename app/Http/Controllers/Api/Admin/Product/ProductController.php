<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use App\Models\Product\Product as RecordModel;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $selectFields = [
        'id',
        'description' ,
        'images' ,
        'origin' ,
        'upc',
        'vendor_upc' ,
        'vendor_sku'
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
                    'description' ,
                    'origin' , 
                    'upc',
                    'vendor_upc' ,
                    'vendor_sku'
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

        $crud->setRelationshipFunctions([
            'units' => ['id','name'] ,
            'stock' => ['id','attribute_id','unit_id','quantity']
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Listing Product by Store
     */
    public function storeProducts(Request $request){
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 10 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;
        $store_id = isset( $request->store_id ) && $request->store_id !== "" ? $request->store_id : false ;

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
            "pivots" => [
                // $unit ?
                // [
                //     "relationship" => 'units',
                //     "where" => [
                //         "in" => [
                //             "field" => "id",
                //             "value" => [$request->unit]
                //         ],
                //     // "not"=> [
                //     //     [
                //     //         "field" => 'fieldName' ,
                //     //         "value"=> 'value'
                //     //     ]
                //     // ],
                //     // "like"=>  [
                //     //     [
                //     //        "field"=> 'fieldName' ,
                //     //        "value"=> 'value'
                //     //     ]
                //     // ]
                //     ]
                // ]
                // : []
                $store_id ?
                [
                    "relationship" => 'stock',
                    "where" => [
                        "in" => [
                            "field" => "store_id",
                            "value" => [ $store_id ]
                        ],
                    // "not"=> [
                    //     [
                    //         "field" => 'fieldName' ,
                    //         "value"=> 'value'
                    //     ]
                    // ],
                    // "like"=>  [
                    //     [
                    //        "field"=> 'fieldName' ,
                    //        "value"=> 'value'
                    //     ]
                    // ]
                    ]
                ]
                : []
            ],
            "pagination" => [
                'perPage' => $perPage,
                'page' => $page
            ],
            "search" => $search === false ? [] : [
                'value' => $search ,
                'fields' => [
                    'description' ,
                    'origin' , 
                    'upc',
                    'vendor_upc' ,
                    'vendor_sku'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields , [
            'images' => function($images){
                return is_array( $images ) && !empty( $images ) ? array_map(function( $image ){ 
                    return \Storage::disk('public')->exists( $image ) ? \Storage::disk("public")->url( $image  ) : "" ;
                }, $images ) : [] ;
            }
        ] );

        $crud->setRelationshipFunctions([
            'units' => ['id','name'] ,
            'stock' => ['id','attribute_id','unit_id','quantity']
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Create
     */
    public function create(Request $request){
        $record = RecordModel::where('description',$request->description)
        ->where('vendor_sku',$request->vendor_sku)
        ->where('vendor_upc',$request->vendor_upc)
        ->first() ;
        if( $record ){
            return response(
                [
                    'record' => $record ,
                    'ok' => false ,
                    'message' => 'ព័តមាន '.$record->name .' មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ], 403
            );
        }else{
            $request->merge(['images' =>[]]);
            $record = new RecordModel(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            $record->save();

            /**
             * Generate UPC for individual product
             */
            $record->upc = "P" . sprintf("%04d", $record->id) . \Carbon\Carbon::today()->format('Ymd');
            $record->save();
            
            if( $record ){
                return response()->json([
                    'record' => RecordModel::find( $record->id ) ,
                    'ok' => true ,
                    'message' => 'បញ្ចូលព័ត៌មានថ្មីបានដោយជោគជ័យ !'
                ], 200);

            }else {
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'មានបញ្ហាក្នុងការបញ្ចូលព័ត៌មានថ្មី !'
                ], 403);
            }
        }
    }
    /**
     * Update
     */
    public function update(Request $request){
        $record = RecordModel::find($request->id);
        if( $record ){
            $record->update(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            return response()->json([
                'record' => $record ,
                'ok' => true ,
                'message' => 'កែប្រែព័ត៌មានរួចរាល់ !'
            ], 200);
        }else{
            return response([
                'record' => $record ,
                'ok' => false ,
                'id' => $request->input() ,
                'message' => 'មិនមានព័ត៌មាននេះឡើង។'
                ], 403);
        }
    }
    /***
     * Read
     */
    public function read($id)
    {
        $record = RecordModel::find($id);
        if ($record) {
            return response()->json([
                'record' => $record,
                'ok' => true ,
                'message' => 'អានទិន្ន័យបានជោគជ័យ !'
            ], 200);
        } else {
            return response()->json([
                'record' => null,
                'ok' => false ,
                'message' => 'មិនមានទិន្នន័យផ្ទៀងផ្ទាត់ ជាមួយលេខសម្គាល់ ដែលអ្នកផ្ដល់អោយឡើយ !'
            ], 403);
        }
    }
    /**
     * Function delete an account
     */
    public function delete($id){
        $record = RecordModel::find($id);
        if( $record ){
            $record->deleted_at = \Carbon\Carbon::now() ;
            $record->save();
            // record does exists
            return response([
                'record' => $record ,
                'ok' => true ,
                'message' => 'បានលុបដោយជោគជ័យ !' 
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមទោស មិនមានព័ត៌មាននេះឡើយ។' ],
                403
            );
        }
    }
    /**
     * Function Restore an account from SoftDeletes
     */
    public function restore($id){
        if( $record = RecordModel::restore($id) ){
            return response([
                'record' => $record ,
                'ok' => true ,
                'message' => 'បានយកត្រឡប់មិវិញដោយជោគជ័យ !'
                ],200
            );
        }
        return response([
                'record' => null ,
                'ok' => false ,
                'message' => 'មិនមានព័ត៌មាននេះឡើយ។'
            ],403
        );
    }
    public function forFilter(Request $request)
    {
        $crud = new CrudController(new RecordModel(), $request, ['id', 'name','upc', 'vendor_upc' , 'vendor_sku']);
        $responseData['records'] = $crud->forFilter();
        $responseData['ok'] = true ;
        $responseData['message'] = __("crud.read.success");
        return response()->json($responseData, 200);
    }
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
                    'name' , 'upc'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, ['id', 'description' ,'origin','upc', 'vendor_upc' ,'vendor_sku'] );
        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at'); 

        $responseData['records'] = $builder->get();
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Upload
     */
    public function upload(Request $request){
        $product = RecordModel::find( $request->id );
        if( $product ){
            $images = $product->images ;
            foreach( $_FILES['files']['tmp_name'] AS $index => $file ){
                $uniqeName = \Storage::disk('public')->putFile( "products/".$product->id, new File( $_FILES['files']['tmp_name'][$index] ) );
                $images[] = $uniqeName ;
            }
            $product->images = $images ;
            $product->save();
            $images = [] ;
            foreach( $product->images AS $index => $image ){
                $images[] = \Storage::disk('public')->exists( $image ) ? \Storage::disk("public")->url( $image  ) : [] ;
            }
            $product->images = $images ;
            return response([
                'record' => $product ,
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

<?php

namespace App\Http\Controllers\Api\Pos;

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
        $user = \Auth::user() ;
        if( $user == null ){
            return response()->json([
                'message' => 'សូមចូលប្រើម្ដងទៀត។'
            ],401);
        }
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 100 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;
        $store_id = isset( $request->store_id ) && $request->store_id > 0  ? $request->store_id : false ;

        if( !$store_id ){
            return response()->json([
                'message' => 'សូមបញ្ជាក់ ហាងជាមុនសិន។'
            ],403);
        }

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
            'images' => function($record){
                return is_array( $record->images ) && !empty( $record->images ) ? array_map(function( $image ){ 
                    return \Storage::disk('public')->exists( $image ) ? \Storage::disk("public")->url( $image  ) : "" ;
                }, $record->images ) : [] ;
            }
        ] );

        $builder = $crud->getListBuilder();
        
        /**
         * Filter the record by the stock unit sku
         */
        // if( $search ){
        //     $builder = $builder->whereHas('stock',function( $stockQuery ) use( $search ){
        //         $stockQuery = $stockQuery->whereHas('stockUnits',function($stockUnitQuery) use ( $search ) {
        //             $words = explode(' ', str_replace(',',' ',$search) );
        //             foreach( $words as $index => $word ) {
        //                 $stockUnitQuery = $index > 0 
        //                     ? $stockUnitQuery->orWhere('sku','like','%'. $word .'%')
        //                     : $stockUnitQuery->where('sku','like','%'. $word .'%') ;
    
        //             }
        //         });
        //     });
        // }

        $responseData = $crud->pagination(true, $builder);
        $responseData['records'] = $responseData['records']->map(function($record) use( $store_id ) {
            $stock = \App\Models\Stock\Stock::where('product_id',$record['id'])->where('store_id',$store_id)->first();
            /**
             * Stock information
            */
            /**
             * Stock Unit
             */
            $stock->stockUnits;
            /**
             * Attribute Variants
             */
            $stock->variants = \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id',
                \App\Models\Product\AttributeVariant::find($stock->attribute_variant_id)->variants
            )->pluck('name') ;
            /**
             * The unit of Stock Unit 
             */
            foreach( $stock->stockUnits AS $index => $stockUnit ){ $stockUnit->unit; }
            $record['stock'] = $stock ;
            return $record;
        });

        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
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
}

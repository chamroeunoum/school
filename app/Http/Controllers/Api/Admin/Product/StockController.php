<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use App\Models\Stock\Stock as RecordModel;
use App\Models\Stock\StockUnit ;

class StockController extends Controller
{
    private $selectFields = [
        'id',
        'store_id' ,
        'product_id' ,
        'attribute_variant_id' ,
        'upc' ,
        'vendor_sku' ,
        'location'
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
            "pivots" => [
                // Pivot relationship to product
                $search ?
                [
                    "relationship" => 'product',
                    "where" => [
                        // "in" => [
                        //     "field" => "id",
                        //     "value" => [$request->unit]
                        // ],
                        // "not"=> [
                        //     [
                        //         "field" => 'fieldName' ,
                        //         "value"=> 'value'
                        //     ]
                        // ],
                        "like"=>  [
                            [
                                "field"=> 'description' ,
                                "value"=> $search
                            ],
                            [
                                "field"=> 'origin' ,
                                "value"=> $search
                            ],
                            [
                                'field' => 'upc' ,
                                'value' => $search
                            ]
                        ]
                    ]
                ]
                : [] ,
                // Pivot relationship to stock unit
                $search ?
                [
                    "relationship" => 'stockUnits',
                    "where" => [
                        // "in" => [
                        //     "field" => "id",
                        //     "value" => [$request->unit]
                        // ],
                        // "not"=> [
                        //     [
                        //         "field" => 'fieldName' ,
                        //         "value"=> 'value'
                        //     ]
                        // ],
                        "like"=>  [
                            [
                                "field"=> 'sku' ,
                                "value"=> $search
                            ]
                        ]
                    ]
                ]
                : [] ,
            ],
            "pagination" => [
                'perPage' => $perPage,
                'page' => $page
            ],
            "search" => $search === false ? [] : [
                'value' => $search ,
                'fields' => [
                    'upc' ,
                    'vendor_sku' ,
                    'location'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields);
        $crud->setRelationshipFunctions([
            'product' => [ 'id', 'description', 'origin', 'upc' ] ,
            'stockUnits' => [ 'id', 'stock_id', 'unit_id', 'quantity', 'sku' , 'unit_price' ] ,
            'attributeVariant' => [ 'id', 'variants' ] ,
            'store' => [ 'id', 'name' , 'location_name' , 'lat_long', 'address' , 'phone' ]
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['records'] = $responseData['records']->map(function($record){

            /**
             * Attribute Variants
             */
            if( !isset( $record['attributeVariant'] ) ){
                $record['attributeVariant']['variants'] = [] ;
            }
            $record['attributeVariant']['variants'] = \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id',$record['attributeVariant']['variants'])->get() ;
            /**
             * Stock Unit
             */
            $record['stockUnits'] = array_key_exists ( 'stockUnits' , $record ) ? array_map(function($stockUnit){
                $stockUnit->unit = 
                \App\Models\Stock\Unit::find( $stockUnit->unit_id );
                return $stockUnit ;
            },$record['stockUnits']) : [] ;


            // if( array_key_exists ( 'stockUnit' , $record ) ) {
            //     $record['stockUnit']['unit'] = \App\Models\Stock\Unit::find( $record['stockUnit']['unit_id'] );
            // }else{
            //     $record['stockUnit'] = null ;    
            // }

            return $record;
        });
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Create
     */
    public function create(Request $request){
        $record = RecordModel::where('product_id',$request->product_id)->first() ;
        if( $record ){
            $record->attributeVariant;
            $record->product;
            return response([
                'record' => $record ,
                'variants' => $record->attributeVariant != null && !empty( $record->attributeVariant->variants ) ? \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id', $record->attributeVariant->variants )->get() : [] ,
                'ok' => false ,
                'message' => 'ព័តមាន '.$record->name .' មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ],403
            );
        }else{

            $attributeVariant = null ;
            if( isset( $request->variants ) && is_array( $request->variants ) && !empty( $request->variants ) ){
                $attributeVariant = \App\Models\Product\AttributeVariant::where( 'variants', $request->variants )->first();
                if( $attributeVariant === null ){
                    $attributeVariant = \App\Models\Product\AttributeVariant::create([ 'variants' => $request->variants ]);
                }
            }

            $record = new RecordModel(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            $record->save();
            
            if( $attributeVariant !== null && $attributeVariant->id > 0 ){
                $record->attribute_variant_id = $attributeVariant->id ;
                $record->save();
            }

            $record->product ;
            $record->attributeVariant;
            $record->transactions;

            if( $record ){
                return response()->json([
                    'record' => $record ,
                    'message' => 'បញ្ចូលព័ត៌មានថ្មីបានដោយជោគជ័យ។' ,
                    'ok' => true ,
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
        $record = RecordModel::
        // where('product_id',$request->product_id)
        // ->where('unit_id',$request->unit_id)
        where('id',$request->id)->first() ;
        if( $record ){
            // Update stock
            $record->update([
                'product_id' => $request->product_id ,
                'location' => $request->location ,
                'vendor_sku' => $request->vendor_sku ,
                'upc' => $request->upc
            ]);

            $attributeVariant = null ;
            if( isset( $request->variants ) && is_array( $request->variants ) && !empty( $request->variants ) ){
                $attributeVariant = \App\Models\Product\AttributeVariant::where( 'variants', implode(',',$request->variants) )->first();
                if( $attributeVariant === null ){
                    $attributeVariant = \App\Models\Product\AttributeVariant::create([ 'variants' => implode(',',$request->variants) ]);
                }
            }
            
            if( $attributeVariant !== null && $attributeVariant->id > 0 ){
                $record->attribute_variant_id = $attributeVariant->id ;
                $record->save();
            }

            $record->attributeVariant ;
            $record->product ;
            $record->transactions;

            if( $record ){
                return response()->json([
                    'record' => $record ,
                    'variants' => $record->attributeVariant != null && !empty( $record->attributeVariant->variants ) ? \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id', $record->attributeVariant->variants )->get() : [] ,
                    'message' => 'កែប្រែព័ត៌មានរួចរាល់។' ,
                    'ok' => true ,
                ], 200);

            }else {
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'មានបញ្ហាក្នុងការប្ដូរព័ត៌មាន។'
                ], 403);
            }
            
        }else{
            return response([
                'record' => null ,
                'ok' => false ,
                'message' => 'ព័តមាន មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ],403
            );
        }
    }
    /***
     * Read
     */
    public function read($id)
    {
        $record = RecordModel::find($id);
        if ($record) {
            $record->product;
            $record->unit;
            $variants = [] ;
            if( $record->attribute_variant_id ){
                $variants = \App\Models\Product\Variant::select(['id','name'])->whereIn('id', \App\Models\Product\AttributeVariant::find( $record->attribute_variant_id )->variants )->get();
            }
            return response()->json([
                'record' => $record,
                'variants' => $variants ,
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
        $crud = new CrudController(new RecordModel(), $request, ['id', 'name']);
        $responseData['records'] = $crud->forFilter();
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
                    'name'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields );
        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at'); 

        $responseData['records'] = $builder->get();
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * List of stock units
     */
    public function stockunits(Request $request){
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 10 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;
        $store_id = isset( $request->store_id ) && $request->store_id > 0 ? $request->store_id : false ;
        $stock_id = isset( $request->stock_id ) && $request->stock_id > 0 ? $request->stock_id : false ;

        $queryString = [
            "where" => [
                'default' => [
                    [
                        'field' => 'stock_id' ,
                        'value' => !$stock_id ? 0 : $stock_id
                    ]
                ],
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
            ] ,
            "pivots" => [
                // Pivot relationship to product
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
                        //         "field"=> 'description' ,
                        //         "value"=> $search
                        //     ],
                        //     [
                        //         "field"=> 'origin' ,
                        //         "value"=> $search
                        //     ],
                        //     [
                        //         'field' => 'upc' ,
                        //         'value' => $search
                        //     ]
                        // ]
                    ]
                ]
                : [
                    "relationship" => 'stock',
                    "where" => [
                        // "default" => [
                        //     'field' => 'store_id' ,
                        //     'value' => []
                        // ]
                        "in" => [
                            "field" => 'store_id' ,
                            "value"=> [ null , 0 ]
                        ],
                        // "not"=> [
                        //     [
                        //         "field" => 'store_id' ,
                        //         "value"=> [ null , 0 ]
                        //     ]
                        // ],
                        // "like"=>  [
                        //     [
                        //         "field"=> 'store_id' ,
                        //         "value"=> null
                        //     ],
                        //     [
                        //         "field"=> 'store_id' ,
                        //         "value"=> 0
                        //     ],
                        //     // [
                        //     //     'field' => 'upc' ,
                        //     //     'value' => $search
                        //     // ]
                        // ]
                    ]
                ] ,
                // Pivot relationship to stock unit
                // $search ?
                // [
                //     "relationship" => 'stockUnits',
                //     "where" => [
                //         // "in" => [
                //         //     "field" => "id",
                //         //     "value" => [$request->unit]
                //         // ],
                //         // "not"=> [
                //         //     [
                //         //         "field" => 'fieldName' ,
                //         //         "value"=> 'value'
                //         //     ]
                //         // ],
                //         "like"=>  [
                //             [
                //                 "field"=> 'sku' ,
                //                 "value"=> $search
                //             ]
                //         ]
                //     ]
                // ]
                // : [] ,
            ],
            "pagination" => [
                'perPage' => $perPage,
                'page' => $page
            ],
            "search" => $search === false ? [] : [
                'value' => $search ,
                'fields' => [
                    'sku'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new StockUnit(), $request, ['id','stock_id','unit_id','quantity','unit_price','sku']);
        $crud->setRelationshipFunctions([
            'stock' => [ 'id', 'store_id' , 'product_id' , 'attribute_variant_id' , 'upc' , 'vendor_sku' , 'location' ] ,
            'unit' => [ 'id', 'name' ] ,
            'transactions' => [ 'id', 'stock_id', 'stock_unit_id' ,'unit_id', 'quantity' , 'user_id' , 'transaction_type_id', 'unit_price', 'parent_stock_id' ]
        ]);

        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['records'] = $responseData['records']->map(function($record){
            $stock = \App\Models\Stock\Stock::find( $record['stock_id'] );
            $record['stock']['product'] = $stock->product;
            /**
             * Attribute Variants
             */
            if( $stock->attribute_variant_id > 0 ){
                if( !isset( $stock->attributeVariant ) ){
                    $stock->attributeVariant->variants = [] ;
                }
                $record['stock']['attributeVariant']['variants'] = \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id',$stock->attributeVariant->variants)->get() ;
            }else{
                $record['stock']['attributeVariant']['variants'] = [];
            }
            
            return $record;
        });
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Stock in
     */
    public function stockIn(Request $request){
        if( $request->stock_id > 0 && $request->quantity > 0 && $request->unit_id > 0 ){
            $stock = RecordModel::find( $request->stock_id );
            if( $stock ){

                /**
                 * Update the unit of the stock base on its unit
                 */
                $stockUnit = \App\Models\Stock\StockUnit::where('stock_id',$stock->id)
                ->where('unit_id', $request->unit_id)->first();
                if( $stockUnit ) {
                    $stockUnit->quantity += $request->quantity ;
                    $stockUnit->unit_price = $request->unit_price ;
                    $stockUnit->save();
                }else{
                    $stockUnit = \App\Models\Stock\StockUnit::create([
                        'stock_id' => $stock->id ,
                        'unit_id' => $request->unit_id ,
                        'quantity' => $request->quantity ,
                        'unit_price' => $request->unit_price ,
                        'sku' => ''
                    ]);
                    $stockUnit->sku = sprintf('%d', $stock->id . \Carbon\Carbon::now()->format('Ymd') );
                    $stockUnit->save();
                }

                /**
                 * Record the quantity of product into stock transaction
                 */
                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $stockUnit->id ,
                    'unit_id' => $request->unit_id ,
                    'quantity' => $request->quantity ,
                    'unit_price' => $request->unit_price ,
                    'user_id' => \Auth::user()->id ,                    
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_in')->first()->id ,
                ]);

                $stock->product;
                $stock->stockUnits;

                return response()->json([
                    'record' => $stock ,
                    'ok' => true ,
                    'message' => 'ដាក់បញ្ចូលចំនួនផលិតផលបានជោគជ័យ។'
                ],200);
            }else{
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'សូមបញ្ជាក់អំពិលេខសម្គាល់ក្នុងឃ្លាំង។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមបំពេញព័ត៌មានអោយបានគ្រប់គ្រាន់។'
            ],403);
        }
    }
    /**
     * Stock out
     */
    public function stockOut(Request $request){
        if( $request->stock_id > 0 && $request->quantity > 0 ){
            $stock = RecordModel::find( $request->stock_id );
            if( $stock ){
                /**
                 * Update the unit of the stock base on its unit
                 */
                $stockUnit = \App\Models\Stock\StockUnit::where('stock_id',$stock->id)
                ->where('unit_id', $request->unit_id)->first();
                if( $stockUnit ) {
                    $stockUnit->quantity -= $request->quantity ;
                }else{
                    return response()->json([
                        'record' => $stock ,
                        'ok' => false ,
                        'message' => 'មិនមានស្តុកសម្រាប់ដក់ឡើយ'
                    ],403);
                }

                /**
                 * Record the quantity of product into stock transaction - stock out
                 */
                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $stockUnit->id ,
                    'unit_id' => $stockUnit->unit_id ,
                    'user_id' => \Auth::user()->id ,
                    'quantity' => $request->quantity ,
                    'unit_price' => $request->unit_price > 0 ? $request->unit_price : $stock->unit_price ,
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_out')->first()->id
                ]);

                $stock->with('stockUnit');
                $stock->with('product');
                return response()->json([
                    'record' => $stock ,
                    'ok' => true ,
                    'message' => 'ដកចេញចំនួនផលិតផលបានជោគជ័យ។'
                ],200);
            }else{
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'សូមបញ្ជាក់អំពិលេខសម្គាល់ក្នុងឃ្លាំង។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមបំពេញព័ត៌មានអោយបានគ្រប់គ្រាន់។'
            ],403);
        }
    }
    /**
     * Stock defeat
     */
    public function stockDefeat(Request $request){
        if( $request->stock_id > 0 && $request->quantity > 0 && $request->unit_id > 0 ){
            $stock = RecordModel::find( $request->stock_id );
            if( $stock ){

                /**
                 * Update the unit of the stock base on its unit
                 */
                $stockUnit = \App\Models\Stock\StockUnit::where('stock_id',$stock->id)
                ->where('unit_id', $request->unit_id)->first();
                if( $stockUnit ) {
                    if( $stockUnit->quantity < $request->quantity ){
                        $stock->product;
                        $stock->stockUnits;
                        return response()->json([
                            'record' => $stock ,
                            'ok' => true ,
                            'message' => 'ចំនួនផលិតផលដែលបានខូច មានចំនួនច្រើនជាងផលិតផលដែលមានក្នុងឃ្លាំង។'
                        ],403);
                    }
                    $stockUnit->quantity -= $request->quantity ;
                    $stockUnit->save();
                }
                /**
                 * Record the quantity of product into stock transaction
                 */
                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $stockUnit->id ,
                    'unit_id' => $request->unit_id ,
                    'quantity' => $request->quantity ,
                    'unit_price' => $request->unit_price ,
                    'user_id' => \Auth::user()->id ,                    
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_defeat')->first()->id ,
                ]);

                $stock->product;
                $stock->stockUnits;

                return response()->json([
                    'record' => $stock ,
                    'ok' => true ,
                    'message' => 'បានដកផលិតផលដែលខូចរួចរាល់។'
                ],200);
            }else{
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'សូមបញ្ជាក់អំពីលេខសម្គាល់ក្នុងឃ្លាំង។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមបំពេញព័ត៌មានអោយបានគ្រប់គ្រាន់។'
            ],403);
        }
    }
    /**
     * Stock lost
     */
    public function stockLost(Request $request){
        if( $request->stock_id > 0 && $request->quantity > 0 && $request->unit_id > 0 ){
            $stock = RecordModel::find( $request->stock_id );
            if( $stock ){

                /**
                 * Update the unit of the stock base on its unit
                 */
                $stockUnit = \App\Models\Stock\StockUnit::where('stock_id',$stock->id)
                ->where('unit_id', $request->unit_id)->first();
                if( $stockUnit ) {
                    if( $stockUnit->quantity < $request->quantity ){
                        $stock->product;
                        $stock->stockUnits;
                        return response()->json([
                            'record' => $stock ,
                            'ok' => true ,
                            'message' => 'ចំនួនផលិតផលដែលបានបាត់ មានចំនួនច្រើនជាងផលិតផលដែលមានក្នុងឃ្លាំង។'
                        ],403);
                    }
                    $stockUnit->quantity -= $request->quantity ;
                    $stockUnit->save();
                }
                /**
                 * Record the quantity of product into stock transaction
                 */
                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $stockUnit->id ,
                    'unit_id' => $request->unit_id ,
                    'quantity' => $request->quantity ,
                    'unit_price' => $request->unit_price ,
                    'user_id' => \Auth::user()->id ,                    
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_lost')->first()->id ,
                ]);

                $stock->product;
                $stock->stockUnits;

                return response()->json([
                    'record' => $stock ,
                    'ok' => true ,
                    'message' => 'បានដកចំនួនផលិតផលដែលបាត់ចេញរួចរាល់។'
                ],200);
            }else{
                return response()->json([
                    'record' => null ,
                    'ok' => false ,
                    'message' => 'សូមបញ្ជាក់អំពីលេខសម្គាល់ក្នុងឃ្លាំង។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមបំពេញព័ត៌មានអោយបានគ្រប់គ្រាន់។'
            ],403);
        }
    }
    /**
     * Stock transfer
     * From :
     *      stock_id , unit_id , quantity
     * To : 
     *      store_id
     * Description:
     *   Before do transfer the stock must be exists and available.
     */
    public function stockTransfer(Request $request){
        if( $request->stock_unit_id > 0 && $request->quantity > 0 && $request->store_id >= 0 ){

            $currentStockUnit = \App\Models\Stock\StockUnit::find( $request->stock_unit_id );
            if( $currentStockUnit ){

                // Current stock
                $currentStock = \App\Models\Stock\Stock::find($currentStockUnit->stock_id);

                /**
                 * Check whether the quantity of the stock unit is bigger than the requested quantity
                 */
                if( $currentStockUnit->quantity < $request->quantity ){
                    $currentStock->product;
                    $currentStock->stockUnits;
                    return response()->json([
                        'stockToBeTransferTo' => null ,
                        'currentStock' => $currentStock ,
                        'ok' => true ,
                        'message' => 'ចំនួនផលិតផលដែលចង់បញ្ចូនចេញ មានចំនួនច្រើនជាងផលិតផលដែលមានក្នុងឃ្លាំង។'
                    ],403);
                }else{
                    // Get the stock of the store which consists of the unit to be transfer to

                    $stockToBeTransferredTo = \App\Models\Stock\Stock::where('store_id',
                    ( $request->store_id == null || $request->store_id < 0 ? 0 : $request->store_id )
                    )
                    ->where('product_id', $currentStock->product_id )
                    ->where('attribute_variant_id',$currentStock->attribute_variant_id)->first();

                    if( $stockToBeTransferredTo == null ){
                        // Create new stock of a store to be transferred to
                        $stockToBeTransferredTo = \App\Models\Stock\Stock::create([
                            'store_id' => ( $request->store_id == null || $request->store_id < 0 ? 0 : $request->store_id ) ,
                            'product_id' => $currentStock->product_id ,
                            'attribute_variant_id' => $currentStock->attribute_variant_id ,
                            'upc' => $currentStock->upc ,
                            'vendor_sku' => $currentStock->vendor_sku ,
                            'location' => '' ,
                            'pid' => 0 
                        ]);
                        // Create stock unit for the above stock
                        $destinationStockUnit = \App\Models\Stock\StockUnit::create([
                            'stock_id' => $stockToBeTransferredTo->id ,
                            'unit_id' => $currentStockUnit->unit_id ,
                            'quantity' => $request->quantity ,
                            'unit_price' => $currentStockUnit->unit_price ,
                            'sku' => ''
                        ]);
                        $destinationStockUnit->sku = sprintf('%d', $destinationStockUnit->id . \Carbon\Carbon::now()->format('Ymd') );
                        $destinationStockUnit->save();
                    }
                    else {
                        $stockToBeTransferredTo = \App\Models\Stock\Stock::where('store_id',
                            ( $request->store_id == null || $request->store_id < 0 ? 0 : $request->store_id )
                        )
                            ->where('product_id', $currentStock->product_id )
                            ->where('attribute_variant_id',$currentStock->attribute_variant_id)
                            ->whereHas('stockUnits',function($query) use($currentStockUnit) { 
                                $query->where('unit_id',$currentStockUnit->unit_id); 
                            })->first();
                        if( $stockToBeTransferredTo ){
                            // The stock unit of the same type to be transfer to is already exists
                            // Increase the quantity of it
                            $destinationStockUnit = $stockToBeTransferredTo->stockUnits->where('unit_id', $currentStockUnit->unit_id )->first();
                            $destinationStockUnit->quantity += $request->quantity ;
                            $destinationStockUnit->save();
                        }else{
                            $stockToBeTransferredTo = \App\Models\Stock\Stock::where('store_id',
                            ( $request->store_id == null || $request->store_id < 0 ? 0 : $request->store_id )
                            )
                            ->where('product_id', $currentStock->product_id )
                            ->where('attribute_variant_id',$currentStock->attribute_variant_id)->first();
                            // Create stock unit for the above stock
                            $destinationStockUnit = \App\Models\Stock\StockUnit::create([
                                'stock_id' => $stockToBeTransferredTo->id ,
                                'unit_id' => $currentStockUnit->unit_id ,
                                'quantity' => $request->quantity ,
                                'unit_price' => $currentStockUnit->unit_price ,
                                'sku' => ''
                            ]);
                            $destinationStockUnit->sku = sprintf('%d', $destinationStockUnit->id . \Carbon\Carbon::now()->format('Ymd') );
                            $destinationStockUnit->save();
                        }
                    }
                    // Decrease the quantity of the current stock unit that we have take some of quantity to the new stock unit
                    $currentStockUnit->quantity -= $request->quantity ;
                    $currentStockUnit->save();
                    // Process the stock transaction of increasing stock
                    $stockTransaction = \App\Models\Stock\StockTransaction::create([
                        'stock_id' => $stockToBeTransferredTo->id ,
                        'stock_unit_id' => $destinationStockUnit->id ,
                        'unit_id' => $destinationStockUnit->unit_id ,
                        'quantity' => $request->quantity ,
                        'unit_price' => $destinationStockUnit->unit_price ,
                        'user_id' => \Auth::user()->id , 
                        'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_transfer_in')->first()->id ,
                    ]);
                    // Process the stock transaction of decreasing stock
                    $stockTransaction = \App\Models\Stock\StockTransaction::create([
                        'stock_id' => $currentStock->id ,
                        'stock_unit_id' => $currentStockUnit->id ,
                        'unit_id' => $currentStockUnit->unit_id ,
                        'quantity' => $request->quantity ,
                        'unit_price' => $currentStockUnit->unit_price ,
                        'user_id' => \Auth::user()->id , 
                        'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_transfer_out')->first()->id ,
                    ]);
                    // Call to update the relationships
                    $stockToBeTransferredTo->product;
                    $stockToBeTransferredTo->stockUnits;
                    $currentStock->product;
                    $currentStock->stockUnits;
                    return response()->json([
                        'stockToBeTransferTo' => $stockToBeTransferredTo ,
                        'currentStock' => $currentStock ,
                        'ok' => true ,
                        'message' => 'ចំនួនផលិតផលបានផ្ទេរចេញបានជោគជ័យ។'
                    ],200);
                }
            }else{
                return response()->json([
                    'record' => $null ,
                    'ok' => true ,
                    'message' => 'សូមបញ្ជាក់អំពីស្តុកដែលត្រូវបញ្ជូនចេញ។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'សូមបំពេញព័ត៌មានអោយបានគ្រប់គ្រាន់។'
            ],403);
        }
    }
    /**
     * Stock Transactions
     */
    public function transactions(Request $request){
        /** Format from query string */
        $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
        $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 100 ;
        $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;

        $stock_id = isset( $request->stock_id ) && $request->stock_id !== "" ? $request->stock_id : false ;

        $queryString = [
            "where" => [
                'default' => [
                    [
                        'field' => 'stock_id' ,
                        'value' => $stock_id
                    ]
                ],
                // 'in' => [] ,
                // 'not' => [] ,
                // 'like' => [
                //     [
                //         'field' => 'number' ,
                //         'value' => $number === false ? "" : $number
                //     ],
                //     [
                //         'field' => 'year' ,
                //         'value' => $date === false ? "" : $date
                //     ]
                // ] ,
            ] ,
            "pivots" => [
                $search ?
                [
                    "relationship" => 'product',
                    "where" => [
                        // "in" => [
                        //     "field" => "id",
                        //     "value" => [$request->unit]
                        // ],
                        // "not"=> [
                        //     [
                        //         "field" => 'fieldName' ,
                        //         "value"=> 'value'
                        //     ]
                        // ],
                        "like"=>  [
                            [
                                "field"=> 'description' ,
                                "value"=> $search
                            ],
                            [
                                "field"=> 'origin' ,
                                "value"=> $search
                            ],
                            [
                                'field' => 'upc' ,
                                'value' => $search
                            ]
                        ]
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
                    'sku'
                ]
            ],
            "order" => [
                'field' => 'created_at' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new \App\Models\Stock\StockTransaction(), $request, ['id','stock_id','stock_unit_id','quantity','user_id','transaction_type_id','unit_price' , 'created_at' ] );
        $crud->setRelationshipFunctions([
            'stock' => ['id', 'store_id', 'product_id', 'attribute_variant_id', 'vendor_sku', 'upc' , 'location' ] ,
            'user' => ['firstname', 'lastname', 'email', 'phone', 'active' ] ,
            'type' => ['id','name','description'] ,
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['records'] = $responseData['records']->map(function($record){
            $record['product'] = \App\Models\Product\Product::find( $record['stock']['product_id'] );
            $record['attributeVariant'] = \App\Models\Product\AttributeVariant::find( $record['stock']['attribute_variant_id'] );
            if( $record['attributeVariant'] ){
                $record['variants'] = \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id', $record['attributeVariant']->variants )->get() ;
            }
            $record['stock']['stockUnits'] = [] ;
            if( isset( $record['stock'] ) ) {
                $record['stock']['stockUnits'] = $record['stock']['id'] > 0 ? \App\Models\Stock\Stock::find( $record['stock']['id'] )->stockUnits : [] ;
            }
            return $record;
        });
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Convert stock from a unit to another
     */
    public function breakdownUnit(Request $request){
        /**
         * stock_id
         */
        if( ( $stock = RecordModel::find( $request->stock_id ) ) != null ){
            /**
             * Stock unit
             */
            $unitConvention = \App\Models\Stock\UnitConvention::where('stock_id',$request->stock_id)->where('id', $request->unit_convention_id )->first();
            $unitConvention->fromUnit ;
            $unitConvention->toUnit ;

            $bigStockUnit = \App\Models\Stock\StockUnit::where('stock_id',$request->stock_id)->where('unit_id',$unitConvention->from_stock_unit_id)->first();
            /**
             * Check the quantity of the big unit to be converted into small unit
             */
            if( $bigStockUnit->quantity >= $request->quantity ){
                $smallStockUnit = \App\Models\Stock\StockUnit::where('stock_id',$request->stock_id)->where('unit_id',$unitConvention->to_stock_unit_id)->first();
                if( $smallStockUnit === null ){
                    /**
                     * Create the stock unit of the small unit to convert into.
                     */
                    $smallStockUnit = \App\Models\Stock\StockUnit::create([
                        'stock_id' => $stock->id ,
                        'unit_id' => $unitConvention->to_stock_unit_id ,
                        'quantity' => 0 ,
                        'unit_price' => isset( $request->unit_price ) ? 
                            ( $request->unit_price > 0 
                                ? $request->unit_price
                                : $bigStockUnit->unit_price / $unitConvention->gaps 
                            ) : $bigStockUnit->unit_price / $unitConvention->gaps,
                        'sku' => ''
                    ]);
                    $smallStockUnit->sku = sprintf('%d', $stock->id . \Carbon\Carbon::now()->format('Ymd') );
                    $smallStockUnit->save();
                }
                /**
                 * Increase the quantity small unit
                 */
                $smallStockUnit->quantity += $request->quantity * $unitConvention->gaps ;
                $smallStockUnit->save();
                // Decrease quantity of big unit
                $bigStockUnit->quantity -= $request->quantity ;
                $bigStockUnit->save();

                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $smallStockUnit->id ,
                    'unit_id' => $unitConvention->to_stock_unit_id ,
                    'quantity' => $request->quantity * $unitConvention->gaps ,
                    'unit_price' => $smallStockUnit->unit_price ,
                    'user_id' => \Auth::user()->id ,                    
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_breakdown')->first()->id ,
                ]);
                return response()->json([
                    'stock' => $stock ,
                    'smallStockUnit' => $smallStockUnit ,
                    'bigStockUnit' => $bigStockUnit ,
                    'ok' => true ,
                    'message' => 'បានបំបែក ផលិតផលរួចរាល់'
                ],200);
            }else{
                return response()->json([
                    'record' => $parentStock ,
                    'ok' => true ,
                    'message' => 'ចំនួនផលិតផលដែលមានក្នុងឃ្លាំង តូចជាចំនួន ដែលនិងត្រូវបំបែក។'
                ],403);
            }
            
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'ស្វែងរកផលិតផលនេះមិនឃើញ។'
            ],403);
        }
    }
    public function buildupUnit(Request $request){
        /**
         * stock_id
         */
        if( ( $stock = RecordModel::find( $request->stock_id ) ) != null ){
            /**
             * Stock unit
             */
            $unitConvention = \App\Models\Stock\UnitConvention::where('stock_id',$request->stock_id)->where('id', $request->unit_convention_id )->first();
            $unitConvention->fromUnit ;
            $unitConvention->toUnit ;

            $smallStockUnit = \App\Models\Stock\StockUnit::where('stock_id',$request->stock_id)->where('unit_id',$unitConvention->from_stock_unit_id)->first();
            // if( $smallStockUnit == null ){
            //     $smallStockUnit = \App\Models\Stock\StockUnit::create([

            //     ]);
            // }
            /**
             * Check the quantity of the small unit are enough to be converted into big unit
             */
            if( ( $smallStockUnit->quantity / abs( $unitConvention->gaps ) ) >= 
            $request->quantity ){
                $bigStockUnit = \App\Models\Stock\StockUnit::where('stock_id',$request->stock_id)->where('unit_id',$unitConvention->to_stock_unit_id)->first();
                if( $bigStockUnit === null ){
                    /**
                     * Create the stock unit of the small unit to convert into.
                     */
                    $bigStockUnit = \App\Models\Stock\StockUnit::create([
                        'stock_id' => $stock->id ,
                        'unit_id' => $unitConvention->to_stock_unit_id ,
                        'quantity' => 0 ,
                        'unit_price' => isset( $request->unit_price ) ? 
                            ( $request->unit_price > 0 
                                ? $request->unit_price
                                : $smallStockUnit->unit_price * abs( $unitConvention->gaps ) 
                            ) : $smallStockUnit->unit_price * abs( $unitConvention->gaps ),
                        'sku' => ''
                    ]);
                    $bigStockUnit->sku = sprintf('%d', $stock->id . \Carbon\Carbon::now()->format('Ymd') );
                    $bigStockUnit->save();
                }
                /**
                 * Increase the quantity small unit
                 */
                $bigStockUnit->quantity += $request->quantity ;
                $bigStockUnit->save();
                // Decrease quantity of big unit
                $smallStockUnit->quantity -= ( $request->quantity * abs( $unitConvention->gaps ) );
                $smallStockUnit->save();

                // Transaction of stock_build_up
                $stockTransaction = \App\Models\Stock\StockTransaction::create([
                    'stock_id' => $stock->id ,
                    'stock_unit_id' => $bigStockUnit->id ,
                    'unit_id' => $unitConvention->to_stock_unit_id ,
                    'quantity' => $request->quantity * $unitConvention->gaps ,
                    'unit_price' => $bigStockUnit->unit_price ,
                    'user_id' => \Auth::user()->id ,                    
                    'transaction_type_id' => \App\Models\Stock\StockTransactionType::where('name','stock_buildup')->first()->id ,
                ]);

                return response()->json([
                    'stock' => $stock ,
                    'smallStockUnit' => $smallStockUnit ,
                    'bigStockUnit' => $bigStockUnit ,
                    'ok' => true ,
                    'message' => 'បង្កើតឯកសារធំនៃផលិតផលរួចរាល់'
                ],200);
            }else{
                return response()->json([
                    'record' => null ,
                    'stockUnit' => $null ,
                    'ok' => false ,
                    'message' => 'មិនមានផលិតផលនិងអាចបណ្ដុំឡើងទៅឯកតាធំបានឡើយ។'
                ],403);
            }
        }else{
            return response()->json([
                'record' => null ,
                'ok' => false ,
                'message' => 'ស្វែងរកផលិតផលនេះមិនឃើញ។'
            ],403);
        }
    }
}

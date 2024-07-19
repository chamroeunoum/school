<?php

namespace App\Http\Controllers\Api\Manager\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use App\Models\Stock\UnitConvention as RecordModel;

class UnitConventionController extends Controller
{
    private $selectFields = [
        'id',
        'stock_id' ,
        'from_stock_unit_id' ,
        'to_stock_unit_id' ,
        'gaps' ,
        'pid' 
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
            "where" => [
                'default' => [
                    [
                        'field' => 'stock_id' ,
                        'value' => $request->stock_id > 0 ? $request->stock_id : false
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
                    'id'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields );
        $crud->setRelationshipFunctions([
            'stock' => ['id','product_id','attribute_variant_id', 'quanaity' , 'unit_id' ] ,
            'fromUnit' => ['id','name'] ,
            'toUnit' => ['id','name']
        ]);

        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);

        $responseData['records'] = $responseData['records']->map(function($record){
            $record['stock']['product'] = \App\Models\Stock\Stock::find( $record['stock']['product_id'] );
            
            $record['stock']['variants'] = $record['stock']['attribute_variant_id'] > 0 ? \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id',
                \App\Models\Product\AttributeVariant::find( $record['stock']['attribute_variant_id'] )->variants
            )->get() : [] ;

            $record['stock']['unit'] = $record['stock']['unit_id'] > 0 ? \App\Models\Stock\Unit::find( $record['stock']['unit_id'] ) : [] ;
            
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
        $record = RecordModel::where('stock_id',$request->stock_id)
        ->where('from_stock_unit_id',$request->from_stock_unit_id)
        ->where('to_stock_unit_id',$request->to_stock_unit_id)
        ->first() ;
        if( $record ){
            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
            return response([
                'record' => $record ,
                'ok' => false ,
                'message' => 'ខ្នាតបំលែងនេះមានរួចហើយ ។'
                ],403
            );
        }else{
            
            $record = new RecordModel(
                [
                    'stock_id' => $request->stock_id ,
                    'from_stock_unit_id' => $request->from_stock_unit_id  ,
                    'to_stock_unit_id' => $request->to_stock_unit_id  ,
                    'gaps' => $request->gaps ,
                    'pid' => 0
                ]
            );
            $record->save();

            $reverse = new RecordModel([
                'stock_id' => $request->stock_id ,
                'from_stock_unit_id' => $request->to_stock_unit_id  ,
                'to_stock_unit_id' => $request->from_stock_unit_id  ,
                'gaps' => -1 * $request->gaps ,
                'pid' => $record->id 
            ]);
            $reverse->save();

            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
            if( $record ){
                return response()->json([
                    'record' => $record ,
                    'reverse' => $reverse ,
                    'message' => 'បញ្ចូលព័ត៌មានថ្មីបានដោយជោគជ័យ !' ,
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
        $record = RecordModel::find($request->id);
        if( $record ){
            $record->update(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
            return response()->json([
                'record' => $record ,
                'ok' => true ,
                'message' => 'កែប្រែព័ត៌មានរួចរាល់ !'
            ], 200);
        }else{
            return response([
                'record' => null ,
                'ok' => false ,
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
            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
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
            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
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
            $record->stock ;
            $record->fromUnit;
            $record->toUnit;
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
                    'id'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, ['id', 'stock_id', 'from_stock_unit_id' , 'to_stock_unit_id' , 'gaps'] );
        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at'); 

        $responseData['records'] = $builder->get();
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
}

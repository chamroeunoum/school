<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use App\Models\VCBModel as RecordModel;

class VCBModelController extends Controller
{
    private $selectFields = [
        'id',
        'table',
        'class'
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
                    'table' , 'class'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, $this->selectFields );
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
        $query = RecordModel::where('name',$request->name);
        if( isset( $request->class ) && $request->class != "" ){
            $query = $query->where('class',$request->class);
        }
        $record = $query->first() ;
        if( $record ){
            return response([
                'record' => $record ,
                'ok' => false ,
                'message' => 'ព័តមាន '.$record->name .' មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ],403
            );
        }else{
            $record = new RecordModel(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            $record->save();
            $record->class;
            $record->fields;
            
            if( $record ){
                return response()->json([
                    'record' => $record ,
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
            $record->class;
            $record->fields;
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
            $record->class;
            $record->fields;
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
            $record->class;
            $record->fields;
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
            $record->class;
            $record->fields;
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
        $crud = new CrudController(new RecordModel(), $request, ['id', 'name','class']);
        if( isset( $request->class ) && $request->class !== 0 ){
            $query = $query->where('class',$request->class);
        }
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
                    'name' , 'class'
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, ['id', 'name','class'] );
        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at');

        $responseData['records'] = $builder->get();
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
}

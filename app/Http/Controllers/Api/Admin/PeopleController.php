<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Mail\MobilePasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\People as RecordModel ;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Storage;


class PeopleController extends Controller
{
    private $selectFields = [
        'id',
        'code' ,
        'firstname' ,
        'lastname' ,
        'gender' ,
        'dob' ,
        'pob' ,
        'nid' ,
        'nationality' ,
        'nation' ,
        'religion' ,
        'contact_address', 
        'mobile_phone', 
        'office_phone', 
        'email', 
        'degree', 
        'skill', 
        'current_career', 
        'organization', 
        'type_id', 
        'member_since'
    ];
    /**
     * Listing function
     */
    public function index(Request $request){
        $crud = new CrudController(new RecordModel(), $request, $this->selectFields );
        $crud->setRelationshipFunctions([
            'user' => []
            /** relationship name => [ array of fields name to be selected ] */
            // "user" => ['id', 'code',  'firstname', 'lastname','enfirstname','enlastname','email', 'phone','dob','member_since'] 
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['message'] = __("crud.read.success");
        return response()->json($responseData, 200);
    }
    /**
     * Create an account
     */
    public function create(Request $request){
        $record = RecordModel::where('firstname',$request->firstname)
            ->where('lastname',$request->lastname)
            ->where('email',$request->email)
            ->first() ;
        if( $record ){
            // អ្នកប្រើប្រាស់បានចុះឈ្មោះរួចរាល់ហើយ
            return response([
                'record' => $record ,
                'message' => 'ព័តមាន '.$record->lastname . ' ' . $record->firstname .' មានក្នុងប្រព័ន្ធរួចហើយ ។'
                ],403
            );
        }else{

            // អ្នកប្រើប្រាស់ មិនទាន់មាននៅឡើយទេ            
            $record = new RecordModel(
                $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
            );
            $record->save();
            $record->selfAssignCode();
            
            if( $record ){
                return response()->json([
                    'record' => RecordModel::find( $record->id ) ,
                    'message' => 'បញ្ចូលព័ត៌មានថ្មីបានដោយជោគជ័យ !'
                ], 200);

            }else {
                return response()->json([
                    'record' => null ,
                    'message' => 'មានបញ្ហាក្នុងការបញ្ចូលព័ត៌មានថ្មី !'
                ], 403);
            }
        }
    }
    /**
     * Create an account
     */
    public function update(Request $request){
        $record = RecordModel::find($request->id);
        if( $record ){
            // អ្នកប្រើប្រាស់មាន
            $record->update(
                $request->except(['user','_token', '_method', 'current_tab', 'http_referrer'])
            );
            return response()->json([
                'record' => $record ,
                'message' => 'កែប្រែព័ត៌មានរួចរាល់ !'
            ], 200);
        }else{
            // អ្នកប្រើប្រាស់មិនមាន
            return response([
                'record' => null ,
                'message' => 'គណនីជាមួយអ៊ីមែល '.$request->email.' មិនមានក្នុងប្រព័ន្ធឡើយ ។'
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
                'message' => 'អានទិន្ន័យបានជោគជ័យ !'
            ], 200);
        } else {
            return response()->json([
                'record' => null,
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
                'message' => 'គណនី '.$record->name.' បានលុបដោយជោគជ័យ !' 
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'record' => null ,
                'message' => 'សូមទោស គណនីនេះមិនមានទេ !' ],
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
                'message' => 'គណនី '.$record->name.' បានយកត្រឡប់មិវិញដោយជោគជ័យ !'
                ],200
            );
        }
        return response([
                'record' => null ,
                'message' => 'មិនមានគណនីនេះឡើយ !'
            ],403
        );
    }
    public function forFilter(Request $request)
    {
        $crud = new CrudController(new RecordModel(), $request, ['id', 'code','firstname','lastname','email','mobile_phone','contact_address','current_career','services_description','skill','organization']);
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
                    'name', 'email', 'username' , 'phone' ,
                ]
            ],
            "order" => [
                'field' => 'id' ,
                'by' => 'desc'
            ],
        ];

        $request->merge( $queryString );

        $crud = new CrudController(new RecordModel(), $request, ['id', 'code','firstname','lastname','email','mobile_phone','contact_address','current_career','skill','organization'] );
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
}

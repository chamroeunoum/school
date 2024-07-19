<?php

namespace App\Http\Controllers\Api\Manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Loan ;
use App\Models\LoanTransaction as RecordModel ;
use App\Models\People;
use App\Http\Controllers\CrudController;


class LoanTransactionController extends Controller
{
    /**
     * Listing function
     */
    public function index(Request $request){
        $crud = new CrudController(new RecordModel(), $request, ['id', 'loan_id','rate', 'transaction_id','amount','repayment_date']);
        $crud->setRelationshipFunctions([
            /** relationship name => [ array of fields name to be selected ] */
            "loan" => ['id', 'member_id', 'code', 'start_date', 'balance','rate'] ,
            "type" => ['id', 'name']
        ]);
        $builder = $crud->getListBuilder();
        $responseData = $crud->pagination(true, $builder);
        $responseData['message'] = __("crud.read.success");
        return response()->json($responseData, 200);
    }
    /**
     * Function delete an account
     */
    public function delete(Request $request){
        /** Check whether the ids is an array or not */
        $ids = is_array( $request->id ) ? $request->id : [$request->id];
        $deletedRecords = RecordModel::whereIn('id',$ids)->get();
        if( $deletedRecords ){
            $deleteRecords = RecordModel::whereIn('id',$ids)->update([
                'deleted_at' => \Carbon\Carbon::now()
            ]);
            // record does exists
            return response([
                'record' => $deleteRecords ,
                'message' => 'ទិន្នន័យបានលុបដោយជោគជ័យ !' 
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'record' => null ,
                'message' => 'សូមទោស ទិន្នន័យនេះមិនមានទេ !' ],
                201
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
                'message' => 'គណនី '.$record->lastname . ' ' . $record->firstname .' បានយកត្រឡប់មិវិញដោយជោគជ័យ !'
                ],200
            );
        }
        return response([
                'record' => null ,
                'message' => 'មិនមានគណនីនេះឡើយ !'
            ],201
        );
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Loan as RecordModel ;
use App\LoanTransaction;
use App\Models\Member;
use App\Http\Controllers\CrudController;


class LoanController extends Controller
{
    private $selectFields = ['id', 'code', 'balance','duration','people_id','reference_member_id','rate','start_date'];
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
                    'code', 'balance', 'start_date'
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
            /** relationship name => [ array of fields name to be selected ] */
            "people" => ['id', 'code',  'firstname', 'lastname','email', 'phone','dob','member_since'] ,
        ]);

        $builder = $crud->getListBuilder()
        ->whereNull('deleted_at');
        if( isset( $request->search['value'] ) && $request->search['value'] != "" ){
            $builder = $builder->orWhereHas( 'people' , function($query) use ($request) {
                $query->where('firstname','LIKE','%'.$request->search['value'].'%')
                ->orWhere('lastname','LIKE','%'.$request->search['value'].'%');
            });
        }
        $responseData = $crud->pagination(true, $builder);
        $responseData['records'] = $responseData['records']->map(function($record){
            $row = \App\Models\Loan::find( $record['id'] );

            // Current balance
            $record['current_balance'] = $row->getBalance();
            // Interest to be paid
            $record['monthly_interest_tobepaid'] = $row->getInterest();

            return $record;
        });
        $responseData['message'] = __("crud.read.success");
        $responseData['ok'] = true ;
        return response()->json($responseData, 200);
    }
    /**
     * Create an account
     */
    public function create(Request $request){
        $record = new RecordModel(
            $request->except(['_token', '_method', 'current_tab', 'http_referrer'])
        );
        // Generate the Saving Code for Saving Account
        $record->save();
        $record->code = $record->people->code .'-L'. sprintf("%04d", $record->id );
        $record->update();
        if( $record ){
            $record -> member ;
            return response()->json([
                'record' => $record,
                'message' => 'គណនីកម្ចីបានបង្កើតសម្រាប់សមាជិក '. $record->people->lastname . ' ' . $record->people->firstname . ' !'
            ], 200);

        }else {
            return response()->json([
                'record' => null ,
                'message' => 'មានបញ្ហាក្នុងការបង្កើតគណនីកម្ចី !'
            ], 201);
        }
    }
    /**
     * Create an account
     */
    public function update(Request $request){
        $record = RecordModel::find($request->id);
        if( $record ){
            // អ្នកប្រើប្រាស់មាន

            $record->people_id = $request->people_id ;
            $record->balance = $request->balance;
            $record->rate = $request->rate ;
            $record->start_date = $request->start_date ;
            $record->duration = $request -> duration ;
            $record->reference_member_id = $request->reference_member_id ;
            $record->save(); 
            // Load member information into record   
            $record->member ;
            return response()->json([
                'record' => $record ,
                'ok' => true ,
                'message' => 'កែប្រែព័ត៌មានរួចរាល់។'
            ], 200);
        }else{
            // អ្នកប្រើប្រាស់មិនមាន
            return response([
                'record' => null ,
                'ok' => false ,
                'message' => 'គណនីជាមួយអ៊ីមែល '.$request->email.' មិនមានក្នុងប្រព័ន្ធឡើយ។'
                ], 403);
        }
    }
    /**
     * Function delete an account
     */
    public function read($id){
        $record = RecordModel::find($id) ;
        if( $record ){
            return response([
                'record' => [
                    'id' => $record->id ,
                    'balance' => $record->balance ,
                    'people' => $record->people === null ? null : $record->people ,
                    'code' => $record->code ,
                    'start_date' => $record->start_date ,
                    'duration' => $record->duration ,
                    'rate' => $record->rate 
                ] ,
                'message' => "អានព័ត៌មានកម្ចីបានជោគជ័យ"
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'record' => null ,
                'message' => 'អានព័ត៌មានកម្ចី មិនបានជោគជ័យ' ],
                422
            );
        }
    }
    /**
     * Function delete an account
     */
    public function delete($id){
        $record = RecordModel::find($id) ;
        if( $record ){
            $record->deleted_at = \Carbon\Carbon::now() ;
            $record->save();
            // record does exists
            return response([
                'record' => $record ,
                'ok' => true ,
                'message' => 'គណនី '.$record->name.' បានលុបដោយជោគជ័យ !' 
                ],
                200
            );
        }else{
            // record does not exists
            return response([
                'record' => null ,
                'ok' => false ,
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
    public function getSchedule(Request $request){
        if( $request->loan_id != "" ){ 
            $loan = \App\Models\Loan::find( $request->loan_id );
            $loan->member;
            if( $loan->duration > 0 ){
                $schedule = $loan->getSchedule();
                return response([
                    'schedule' => $schedule["schedule"] ,
                    'repayment_amount' => $schedule['repayment_amount'] ,
                    'totalInterest' => $schedule['totalInterest'] ,
                    'totalRepayback' => $schedule['totalRepayback'] ,
                    'totalAmountTobePaid' => $schedule['totalAmountTobePaid'] ,
                    'loan' => $loan ,
                    'ok' => true ,
                    'message' => 'អានព័ត៌មានបានជោគជ័យ !'
                ],200);
            }else{
                return response([
                    'schedule' => 0 ,
                    'repayment_amount' => 0 ,
                    'totalInterest' => 0 ,
                    'loan' => $loan ,
                    'ok' => false ,
                    'message' => 'កម្ចីមិនបានបញ្ចាក់អំពី អាយុកាលកម្ចី !'
                ],201);
            }
        }else{
            return response([
                'records' => null ,
                'ok' => false ,
                'message' => 'សូមពិនិត្យ គណនីអោយបានត្រឹមត្រូវជាមុនសិន !'
            ],201);
        }
    }
    public function getTransactions(Request $request){
        $loan = null ;
        if( $request->loan_id ) $loan = \App\Models\Loan::find( $request->loan_id ) ;
        else if( \Auth::user() ) {
            /** Check whether this account connected to any member */
            if( \Auth::user()->people === null ){
                $loan = null ;
                return response()->json([
                    'message' => 'គណនីនេះ មិនទាន់មានកម្ចីឡើយ ! (សូមភ្ជាប់ ព័ត៌មានសមាជិក !)'
                ],201);
            }
            /** Check whether user has any loans or not  */
            if( \Auth::user()->people->loans->count() ){
                $loan = \Auth::user()->people->loans->first();
            }else{
                $loan = null;
                return response()->json([
                    'record' => null,
                    'message' => 'គណនីនេះ មិនទាន់មានកម្ចីឡើយ !'
                ],201);
            }
        } 
        if( $loan ){
            $loan->balance = doubleval( $loan->balance );
            $loan->people;

            $transactions = \App\Models\LoanTransaction::where('loan_id', $loan->id )
            // ->whereIn('transaction_id',[7])
            ->orderby('id','DESC')
            ->get()->map(function( $record , $key ) use ( $loan ) {
                $record->type ;
                $record->repayment_date = \Carbon\Carbon::parse( $record->repayment_date )->format( "Y-m-d , h:i" );
                return [
                    "id" => (int) $record->id,
                    "loan_id" => (string) $loan->id,
                    "transaction_id" => (string) $record->transaction_id,
                    "amount" => (float) $record->amount,
                    "rate" => (float) $record->rate ,
                    "current_balance" => (float) (
                            $record->type->id == 4 
                            ? $record->getBalance() - $record->amount // Repayback
                            : (
                                $record->type->id == 5 
                                ? $record->getBalance() + $record->amount
                                : $record->getBalance()
                            ) //
                        )
                        ,
                    "repayment_date" => (string) \Carbon\Carbon::parse($record->repayment_date)->format('Y-m-d'),
                    "type" => $record->type !== null
                        ?
                        [
                            "id" => (int) $record->type->id,
                            "name" => (string) $record->type->name,
                            "icon" => (string) $record->type->icon,
                            "color" => $record->type->color
                        ]
                        : null 
                ] ;
            });

            return response([
                'records' => $transactions ,
                'loan' => [
                    'id' => $loan->id ,
                    'code' => (string) $loan->code ,
                    'people_id' => (string) $loan->people_id ,
                    'start_date' => (string) \Carbon\Carbon::parse($loan->start_date)->format('Y-m-d') ,
                    'rate' => (float) ( $loan->rate ) ,
                    'balance' => (float) $loan->balance ,
                    'duration' => (float) $loan->duration ,
                    'people' => $loan->people !== null 
                        ?
                            [
                                'id' => $loan->people->id ,
                                'code' => $loan->people->code ,
                                'firstname' => $loan->people->firstname ,
                                'lastname' => $loan->people->lastname
                            ]
                        : null
                ],
                'totalRepayback' => (float) $loan->getTotalRepaybackAmounts() ,
                'totalInterestPaid' => (float) $loan->getTotalInterestAmounts() ,
                'totalInterestOwned' => (float) $loan->getTotalOwnedInterestAmounts() ,
                'totalLoanAmountIncrease' => (float) $loan->getTotalSubLoanAmounts() ,
                'currentBalance' => (float) $loan->getBalance(),
                'message' => 'អានព័ត៌មានបានជោគជ័យ !' ,
                'ok' => true ,
            ],200);
        }else{
            return response([
                'records' => null ,
                'ok' => false ,
                'message' => 'សូមពិនិត្យ ព័ត៌មានអំពីកម្ចីនេះអោយបានត្រឹមត្រូវជាមុនសិន !'
            ],201);
        }
    }

    public function getTransaction(Request $request)
    {
        if ($request->id != '' && (($transaction = \App\Models\LoanTransaction::find($request->id)) !== null)) {
            $loan = $transaction->loan !== null ? $transaction->loan : null;
            $type = $transaction->type !== null ? $transaction->type : null;
            if ($transaction) {
                return response([
                    'record' => [
                        "id" => $transaction->id,
                        "loan_id" => $loan->id,
                        "transaction_id" => $transaction->transaction_id,
                        "amount" => $transaction->amount,
                        "rate" => $transaction->rate,
                        "repayment_date" => $transaction->repayment_date,
                        "repayback" => $transaction->repayback,
                        "interest" => $transaction->interest,
                        "type" => $transaction->type !== null
                            ? [
                                "id" => $transaction->type->id,
                                "name" => $transaction->type->name,
                                "icon" => $transaction->type->icon,
                                "color" => $transaction->type->color
                            ]
                            : null
                    ],
                    'loan' => [
                        'id' => $loan->id,
                        'code' => $loan->code,
                        'people_id' => $loan->people_id,
                        'start_date' => \Carbon\Carbon::parse($loan->start_date)->format('Y-m-d'),
                        'rate' => $loan->rate,
                        'balance' => $loan->balance,
                        'duration' => $loan->duration,
                        'people' => $loan->people !== null
                            ?
                            [
                                'id' => $loan->people->id,
                                'code' => $loan->people->code,
                                'firstname' => $loan->people->firstname,
                                'lastname' => $loan->people->lastname
                            ]
                            : null
                    ],
                    'message' => 'អានព័ត៌មានបានជោគជ័យ !'
                ], 200);
            }
            return response([
                'transaction' => null,
                'message' => 'មិនមានប្រតិបត្តិការនេះទេ !'
            ], 201);
        } else {
            return response([
                'transaction' => null,
                'message' => 'ប្រតិបត្តិការនេះមិនមានទេ !'
            ], 201);
        }
    }
    
    /**
     * Get Loan Repayment
     */
    public function repayment( Request $request ){
        // Get Total Repayback of previous repayment
        $loan = \App\Models\Loan::find($request->loan_id);
        // Check whether this loan has finished repayback
        if( $loan->getBalance() <= 0 ){
            // Loan has finished repayback
            return response([
                'record' => $loan ,
                'message' => 'គណនីកម្ចីនេះ បានសង់ត្រឡប់រួច នៅថ្ងៃ '. $loan->repayments->orderby('id','desc')->first()->repayment_date .' !'
            ],200);
        }
        $records = [] ;
        /** if the repayback amount is smaller than the interest then cancel the repayment process */
        if( $request->amount < $loan->getInterest() ){
            $amount = $request->amount ;
            /** Pay for the interest */
            $record = new \App\Models\LoanTransaction();
            $record->loan_id = $loan->id ;
            $record->transaction_id = 7 ; // Loan interest
            $record->amount = $request->amount ;
            $record->repayment_date = $request->repayment_date ;
            $record -> rate = $loan->rate;
            $record -> save();
            $record->type;
            $record->loan;
            $records[] = $record ;
            /** Own the interest */
            $ownedInterest = $loan->getInterest() - $amount ;
            $record = new \App\Models\LoanTransaction();
            $record->loan_id = $loan->id ;
            $record->transaction_id = 8 ; // owned interest of loan
            $record->amount = $ownedInterest ;
            $record->repayment_date = $request->repayment_date ;
            $record -> rate = $loan->rate;
            $record -> save();
            $record->type;
            $record->loan;
            $records[] = $record ;
            return response([
                'record' => $records ,
                'message' => 'ប្រាក់សំណង មិនគ្រប់គ្រាន់ ! សមាជិកនៅជំពាក់ប្រាក់ការចំនួន ' . ( $ownedInterest )  . '​ USD សម្រាប់ខែនេះ។' 
            ], 200);
        }
        else if( $request->amount >= $loan->getInterest() ){
            /** Create transaction of interest */
            $record = new \App\Models\LoanTransaction();
            $record->loan_id = $loan->id ;
            $record->transaction_id = 7 ;
            $record->amount = $loan->getInterest() ;
            $record->repayment_date = $request->repayment_date ;
            $record -> rate = $loan->rate;
            $record -> save();
            $record->type;
            $record->loan;
            $records[] = $record ;
            if( $request->amount > $loan->getInterest() ){
                /** Create transaction of repayback */
                $record = new \App\Models\LoanTransaction();
                $record->loan_id = $loan->id ;
                $record->transaction_id = 4 ;
                $record->amount = $request->amount - $loan->getInterest() ;
                $record->repayment_date = $request->repayment_date ;
                $record -> rate = $loan->rate;
                $record -> save();
                $record->type;
                $record->loan;
                $records[] = $record ;
            }
        }
       
        if( count( $records ) ){
            return response([
                'record' => $records ,
                'message' => 'សងប្រាក់បានជោគជ័យបានជោគជ័យ !'
            ],200);
        }
        return response([
            'record' => null ,
            'message' => 'សងប្រាក់បានបរាជ័យ !'
        ],201);
    }
    /**
     * Add more loan
     *
     * @param Request $request
     * @return void
     */
    public function addmoreloan(Request $request){
        // Get Total Repayback of previous repayment
        $loan = \App\Models\Loan::find($request->loan_id);
        /** Check the amount before process the loan */
        if( $request->amount > 0 ){
            /** Pay for the interest */
            $record = new \App\Models\LoanTransaction();
            $record->loan_id = $loan->id ;
            $record->transaction_id = 5 ; // Loan interest
            $record->amount = $request->amount ;
            $record->repayment_date = $request->repayment_date ;
            $record -> rate = $loan->rate;
            $record -> save();
            $record->type;
            $record->loan;
            return response([
                'record' => $record ,
                'message' => 'ថែមកម្ចីរួចរាល់ ។' 
            ], 200);
        }
        return response([
            'record' => null ,
            'message' => 'មិនអាចថែមកម្ចីបាន !'
        ],201);
    }
    /**
     * Create an saving account with member code
     */
    public function importAccounts(Request $request)
    {
        if (isset($request->accounts) && is_array($request->accounts)) {
            $successAccounts = [];
            $failedAccounts = [];
            foreach ($request->accounts as $index => $account) {
                $member = \App\Models\People::where('code', strtoupper($account['code']))->first() ;
                if ( $member === null) {
                    /** Create Data Relationship To Member */
                    list($lastname, $firstname) = explode(" ",str_replace([ " ", " ", " "]," ", $account['name'])) ;
                    $people = new \App\Models\People();
                    $people->firstname = $firstname;
                    $people->lastname = $lastname;
                    $people->member_since = $account['start_date'];
                    $people->save();
                    $people->code = $account['code'];
                    $people->save();
                }
                /** Check whether the user has loan account */
                if( $people->hasLoan() ){
                    /** Create the loan transaction of the loan to increate the number of loan */
                    /** Get loan account */
                    $record = $people->loans()->first();
                    /** Create loan transaction */
                    $transaction = new \App\Models\LoanTransaction();
                    $transaction->loan_id = $record->id;
                    $transaction->transaction_id = 5 ; // Add more loan amount
                    $transaction->amount = $account['balance'];
                    $transaction->repayment_date = $account['start_date'];
                    $transaction->rate = $account['rate'];
                    $transaction->save();

                    if( $account['rate'] > 0 ){
                         $record->rate = $account['rate'];
                         $record->save();
                    }
                    if ($account['duration'] > 0) {
                        $record->duration = $record->duration + $account['duration'];
                        $record->save();
                    }
                    $record->people;
                    $successAccounts[] = $record;
                }else{
                    /** Initial new loan for member */
                    // Process creating loan account
                    $record = new RecordModel();
                    $record->people_id = $people->id;
                    $record->start_date = $account['start_date'];
                    $record->rate = $account['rate'];
                    $record->duration = $account['duration'];
                    $record->balance = $account['balance'];
                    $record->reference_member_id = $account['reference_member_id'];
                    $record->save();
                    // $record->code = 'M' . $record->member->id . '-' . $record->member->code . '-L' . $record->id;
                    $record->code = $record->people->code;
                    $record->update();
                    $record->member;
                    $successAccounts[] = $record;
                }
            }
            return response()->json([
                'records' => [
                    'success' => $successAccounts,
                    'failed' => $failedAccounts
                ],
                'message' => "គណនីដែលនាំចូលបានជោគជ័យមានចំនួន ៖ " . count($successAccounts) . " !"
            ], 200);
        } else {
            return response()->json([
                'record' => null,
                'message' => 'មិនមានគណនីឡើយ !'
            ], 201);
        }
    }
    /**
     * Create an saving transactions with member code
     */
    public function importTransactions(Request $request)
    {
        if (isset($request->transactions) && is_array($request->transactions)) {
            $transactions = $request->transactions;
            $failed = $success = [] ;
            foreach ($request->transactions as $index => $transaction) {
                if ($loan = RecordModel::where('code', strtoupper($transaction['code']))->first()) {
                    $currentLoanBalance = $loan->balance - $loan->getTotalRepayback();
                    // if ($transaction['amount'] > 0 && ($transaction['amount'] >= ($currentLoanBalance * ($loan->rate / 100)) ) ) {
                    /**
                     * loan_id, transasction_id, repayment_date, amount
                     */
                    /** Interest */
                    if ($transaction['interest'] > 0) {
                        $record = new \App\Models\LoanTransaction();
                        $record->transaction_id = 7;
                        $record->balance = $currentLoanBalance;
                        $record->loan_id = $loan->id;
                        $record->amount = $transaction['interest'];
                        $record->repayment_date = $transaction['repayment_date'];
                        $record->rate = $loan->rate;
                        $record->save();
                        $transactions[$index]['success'] = 1;
                        $success[] = $transaction;
                    }
                    /** Repayback */
                    if($transaction['repayback']>0){
                        $record = new \App\Models\LoanTransaction();
                        $record->transaction_id = 4;
                        $record->balance = $currentLoanBalance;
                        $record->loan_id = $loan->id;
                        $record->amount = $transaction['repayback'];
                        $record->repayment_date = $transaction['repayment_date'];
                        $record->rate = $loan->rate;
                        $record->save();
                        $transactions[$index]['success'] = 1;
                        $success[] = $transaction;
                    }
                    /** Add more loan balance */
                    if($transaction['loan']>0){
                        $record = new \App\Models\LoanTransaction();
                        $record->transaction_id = 5;
                        $record->balance = $currentLoanBalance;
                        $record->loan_id = $loan->id;
                        $record->amount = $transaction['loan'];
                        $record->repayment_date = $transaction['repayment_date'];
                        $record->rate = $loan->rate;
                        $record->save();
                        $transactions[$index]['success'] = 1;
                        $success[] = $transaction;
                    }
                }
                else {
                    $transactions[$index]['success'] = 0;
                    $failed[] = $transaction;
                }
            }
            return response()->json([
                'transactions' => $transactions,
                'success' => $success ,
                'failed' => $failed ,
                'message' => "ប្រតិបត្តិការដែលនាំចូលបានជោគជ័យមានចំនួន ៖ " . count( $success ) . " និងបរាជ័យ " . count( $failed ) . " !"
            ], 200);
        } else {
            return response()->json([
                'record' => null,
                'message' => 'មិនមានប្រតិបត្តិការសន្សំឡើយ !'
            ], 201);
        }
    }
}

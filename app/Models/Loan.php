<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    
    protected $fillable = ['people_id','balance','duration','rate','reference_member_id','start_date','code'];

    protected $casts = [
        'code' => 'string' ,
        'people_id' => 'int' ,
        'balance' => 'double' ,
        'start_date' => 'date' ,
        'duration' => 'int' ,
        // 'rate' => 'double' ,
        'pdf' => 'string'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    
    /** 
     * Get total balance of all loans
     */
    public static function getTotalBalances($startDate = false)
    {
        return $startDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? static::all()->sum('balance') + \App\Models\LoanTransaction::where('transaction_id',5)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : static::where('start_date', 'LIKE', $startDate . '%')->sum('balance') + \App\Models\LoanTransaction::where('transaction_id',5)->where('repayment_date', 'LIKE', $startDate . '%')->sum('amount');
    }

    /**
     * Get total repayback of principle of all loans
     */
    public static function getTotalPrinciples($startDate = false)
    {
        return $startDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? \App\Models\LoanTransaction::where('transaction_id',4)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : \App\Models\LoanTransaction::where('transaction_id',4)->where('repayment_date', 'LIKE', $startDate . '%')->sum('amount');
    }

    /**
     * Get total interest of all loans
     */
    public static function getTotalInterests($startDate = false)
    {
        return $startDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? \App\Models\LoanTransaction::where('transaction_id',7)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : \App\Models\LoanTransaction::where('transaction_id',7)->where('repayment_date', 'LIKE', $startDate . '%')->sum('amount');
    }

    /**
     * Get total owned interest of all loans
     */
    public static function getTotalOwnedInterests($startDate = false)
    {
        return $startDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? \App\Models\LoanTransaction::where('transaction_id',8)->where('repayment_date', 'LIKE', $startDate . '%')->sum('amount')
            : \App\Models\LoanTransaction::where('transaction_id',8)->sum('amount');
    }

    /**
     * Get total repayback of all loans
     */
    public static function getTotalRepaybacks($startDate = false)
    {
        return $startDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? \App\Models\LoanTransaction::where('transaction_id',4)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : \App\Models\LoanTransaction::where('transaction_id',4)->where('repayment_date', 'LIKE', $startDate . '%')->sum('amount');
    }


    /** 
     * Get repayback transactions
     */
    public function getRepaybackTransactions($repaymentDate = false)
    {
        $qb = $this->transactions()->where('transaction_id', 4);
        return $repaymentDate
            /** Return repayback transactions without specific date (month) [YYYY-mm] */
            ? $qb
            /** Return repayback transactions with specific date (month) [YYYY-mm] */
            : $qb->where('repayment_date', 'LIKE', $repaymentDate . '%');
    }
    /** 
     * Get total amount of repayback
     */
    public function getTotalRepaybackAmounts($repaybackDate = false)
    {
        return (float) $this->getRepaybackTransactions($repaybackDate)->sum('amount');
    }
    /** 
     * Get repayback transactions
     */
    public function getRepaybackTransactionsBy($repaymentDate)
    {
        $qb = $this->transactions()->where('transaction_id', 4);
        return $qb->where('repayment_date', '<=', $repaymentDate);
    }
    /** 
     * Get total amount of repayback
     */
    public function getTotalRepaybackAmountsBy($repaybackDate = false)
    {
        return (float) $this->getRepaybackTransactionsBy($repaybackDate)->sum('amount');
    }
    /** 
     * Get interest transactions
     */
    public function getInterestTransactions($repaybackDate = false)
    {
        $qb = $this->transactions()->where('transaction_id', 7);
        return $repaybackDate
            /** Return interest transactions without specific date (month) [YYYY-mm] */
            ? $qb
            /** Return interest transactions with specific date (month) [YYYY-mm] */
            : $qb->where('repayment_date', 'LIKE', $repaybackDate . '%');
    }
    /** 
     * Get total amount of interests
     */
    public function getTotalInterestAmounts($repaybackDate = false)
    {
        return (float) $this->getInterestTransactions($repaybackDate)->sum('amount');
    }

    /** 
     * Get owned interest transactions
     */
    public function getOwnedInterestTransactions($repaybackDate = false)
    {
        $qb = $this->transactions()->where('transaction_id', 8);
        return $repaybackDate
            /** Return interest transactions without specific date (month) [YYYY-mm] */
            ? $qb
            /** Return interest transactions with specific date (month) [YYYY-mm] */
            : $qb->where('repayment_date', 'LIKE', $repaybackDate . '%');
    }
    /** 
     * Get total amount of interests
     */
    public function getTotalOwnedInterestAmounts($repaybackDate = false)
    {
        return (float) $this->getOwnedInterestTransactions($repaybackDate)->sum('amount');
    }

    /** 
     * Get deposit transactions
     */
    public function getSubLoanTransactions($repaybackDate = false)
    {
        $qb = $this->transactions()->where('transaction_id', 5);
        return $repaybackDate
            /** Return deposit transactions without specific date (month) [YYYY-mm] */
            ? $qb
            /** Return deposit transactions with specific date (month) [YYYY-mm] */
            : $qb->where('repayment_date', 'LIKE', $repaybackDate . '%');
    }
    /** 
     * Get total amount of deposits
     */
    public function getTotalSubLoanAmounts($repaybackDate = false)
    {
        return (float) $this->getSubLoanTransactions($repaybackDate)->sum('amount');
    }

    /** 
     * Get deposit transactions
     */
    public function getSubLoanTransactionsBy($repaybackDate)
    {
        $qb = $this->transactions()->where('transaction_id', 5);
        return $qb->where('repayment_date', '<=', $repaybackDate );
    }
    /** 
     * Get total amount of deposits
     */
    public function getTotalSubLoanAmountsBy($repaybackDate)
    {
        return (float) $this->getSubLoanTransactionsBy($repaybackDate)->sum('amount');
    }

    /**
     * Get current balance of a saving account
     */
    public function getBalance($repaybackDate = false)
    {
        return
            /** Start loan balance */
            $this->balance
            /** Total sub balance */
            + $this->getTotalSubLoanAmounts($repaybackDate)
            /** Total repayback Amount */
            - $this->getTotalRepaybackAmounts($repaybackDate);
    }
    /**
     * Get current balance of a saving account
     */
    public function getBalanceBy($repaybackDate)
    {
        return
            /** Start loan balance */
            $this->balance
            /** Total sub balance */
            + $this->getTotalSubLoanAmountsBy($repaybackDate)
            /** Total repayback Amount */
            - $this->getTotalRepaybackAmountsBy($repaybackDate);
    }
    public function getInterest($repaybackDate = false){
        return $this->getBalance($repaybackDate) * ( $this->rate / 100 ) ;
    }
    /**
     * Calculation of Loan within a month
     */
    public static function loanCalculationWithMonth($loan_balance,$rate,$months,$start_date,$schedule=true){
        $loan = new \stdClass();
        // Basic information of loan
        $loan->loan_balance = $loan_balance ;
        $loan->rate = $rate ;
        $loan->start_date = $start_date ;
        $loan->months = $months ;
        $loan->total_interest_rate = 0 ;
        $loan->total_repayment = 0 ;
        // Calculation the "Repayment balance"
        $loan->base_payment_remain = $loan->months > 0 ? $loan->loan_balance % $loan->months : 0 ;
        $loan->base_payment = $loan->months > 0 ? ( $loan->loan_balance - $loan->base_payment_remain ) / $loan->months : 0 ;

        /* Generate the repayment schedule */
        $loan->schedule = array();
        if( $schedule ){
            for($step=1;$step<$loan->months;$step++){
                $date_to_pay = strtotime( "+".$step." month", strtotime( $loan->start_date ) );
                $loan->schedule[$step]=[
                    'step' => $step ,
                    'date' => $date_to_pay ,
                    'rate' => $loan->rate ,
                    'interest_rate' => ( $loan->loan_balance - ( $loan->base_payment * ( $step - 1 ) ) ) * ( $loan->rate ) ,
                    'base_payment' => $loan->base_payment ,
                    'repayment' => $loan->base_payment + ( ( $loan->loan_balance - ( $loan->base_payment * ( $step - 1 ) ) ) * ( $loan->rate ) ) ,
                    'loan_balance' => $loan->loan_balance - ( $loan->base_payment * $step ),
                ];
                $loan->total_interest_rate += $loan->schedule[$step]['interest_rate'] ;
                $loan->total_repayment += $loan->schedule[$step]['repayment'] ;
            }
    
            $date_to_pay = strtotime( "+".$loan->months." month", strtotime( $loan->start_date ) );
            $loan->schedule[$loan->months]=[
                'step' => $loan->months ,
                'date' => $date_to_pay ,
                'rate' => $loan->rate ,
                'interest_rate' => ( $loan->base_payment + $loan->base_payment_remain )  * ( $loan->rate ) ,
                'base_payment' => $loan->base_payment + $loan->base_payment_remain ,
                'repayment' => $loan->base_payment + + $loan->base_payment_remain + ( ( $loan->loan_balance - ( $loan->base_payment * ( $step - 1 ) ) ) * ( $loan->rate ) ) ,
                'loan_balance' => $loan->loan_balance - ( $loan->base_payment * $step ),
            ];
            $loan->total_interest_rate += $loan->schedule[$loan->months]['interest_rate'] ;
            $loan->total_repayment += $loan->schedule[$loan->months]['repayment'] ;
        }
        
        return $loan;
    }

    public static function loanCalculationWithRepayment($loan){

        $repayments = array() ;

        $repayment_records = \App\Models\RepaymentSchedule::where('loan_id',$loan->id)->get();
        $loan_balance = $loan->balance ;

        foreach( $repayment_records AS $repayment ){

            $loan_record = new \StdClass();
            // get the loan balance
            $loan_record->loan_balance = $loan_balance ;
            // get the rate of the loan
            // $loan_record->rate = \App\Models\RateBaseOnFlexibility::getLoanRate();
            $loan_record->rate = $loan->rate_type_id ;
            // get the date of repayment
            $loan_record->date = $repayment->repayment_date ;
            // get the repayment of client
            $loan_record->repayment = $repayment->repayment_amount ;
            // calculate the interest of loan base on the loan balance
            $loan_record->interest = $loan_record->loan_balance * ( $loan_record->rate );
            // calculate the interest per day [ assume that there are Config::get('settings.total_days_per_month') days per month - fixed ]
            $loan_record->interest_perday = $loan_record->interest / \App\Models\Setting::getTotalDaysAsMonth() ;
            // calculate the repayment base balance
            $loan_record->repayment_base = $loan_record->repayment - $loan_record->interest ;
            // calculate the remain loan balance after repayment this time
            $loan_record->loan_balance_remain = $loan_record->loan_balance - $loan_record->repayment_base ;

            // calculate the remain loan balance after repayment this time to use with next repayment calculation
            $loan_balance = $loan_balance - $loan_record->repayment_base ;

            // save the loan_record to repayments 
            $repayments[] = $loan_record ;
        }

        return $repayments ;
    }

    public static function loanCalculationWithRepaymentReceived($loan){

        $repayments = array() ;

        $repayment_records = \App\Models\RepaymentSchedule::where('loan_id',$loan->id)->get();
        $loan_balance = $loan->balance ;

        foreach( $repayment_records AS $repayment ){

            $loan_record = new \StdClass();
            // get the loan balance
            $loan_record->loan_balance = $loan_balance ;
            // get the rate of the loan
            // $loan_record->rate = \App\Models\RateBaseOnFlexibility::getLoanRate() * 0.01;
            $loan_record->rate = $loan->rate_type_id ;
            // get the date of repayment
            $loan_record->date = $repayment->repayment_date ;
            // get the repayment of client
            $loan_record->repayment = $repayment->repayment_amount ;
            // calculate the interest of loan base on the loan balance
            $loan_record->interest = $loan_record->loan_balance * ( $loan_record->rate );
            // calculate the interest per day [ assume that there are 30 days per month - fixed ]
            $loan_record->interest_perday = $loan_record->interest / \App\Models\Setting::getTotalDaysAsMonth() ;
            // calculate the repayment base balance
            $loan_record->interest_received = $repayment->interest_rate ;
            $loan_record->interest_owned = round( $repayment->interest_rate - $loan_record->interest , 2 );
            // calculate the remain loan balance after repayment this time
            $loan_record->loan_balance_remain = $loan_record->loan_balance - $loan_record->repayment ;
            // calculate the remain loan balance after repayment this time to use with next repayment calculation
            $loan_balance = $loan_record->loan_balance - $loan_record->repayment ;

            // save the loan_record to repayments 
            $repayments[] = $loan_record ;
        }

        return $repayments ;
    }

    public function getDeanline(){
        $startDate = \Carbon\Carbon::parse( $this->start_date );
        return $startDate->addMonths( $this->duration_as_month );
    }
    public function isRepaybackedForColumn(){
        return $this->isRepaybacked()
        // បើថ្ងៃនេះ ធំជាង អាយុកាលកំណត់ លទ្ធផល និងវិជ្ជាមាន
        ? "<label class='label label-info' >បញ្ចប់ជាស្ថាព័រ</label>"
        // បើថ្ងៃនេះ តូចជាង អាយុកាលកំណត់ លទ្ធផល និងអវិជ្ជមាន
        : "<label class='label label-success' >នៅជំពាក់</label>" ;
    }
    public function isRepaybacked(){
        $repayment = $this->repayments->first() ;
        return ( $repayment !== null && (int)$repayment->getTotalBalanceAfterRepayback() === 0 )
        // The loan balance is equal the balance of repayment back
        ? true
        // The loan balance is not equal the balance of repayment back
        : false ;
    }
    public static function isAccountRepaybacked($loanId){
        return static::find( $loanId )->isRepaybacked() ;
    }
    public static function getTotalLoanHasRepaybacked(){
        $loans = static::all();
        $totalAmount = 0 ;
        foreach( $loans AS $index => $loan ){
            if( $loan->repayments->count() ){
                // the repayment has been made and completed
                $totalAmount += $loan->repayments->first()->getTotalRepayment() ;
            }
        }
        return $totalAmount ;
    }
    /**
     * Function: check whether provided member has the account already
     * return : 
     *  1. Object of the account if exists
     *  2. false if does not exist
     */
    public static function hasAccount($memberId){
        $result = static::where('people_id',$memberId)->first();
        return is_object( $result ) ? $result : false ;
    }
    public function isFirst(){
        return $this->where('loan_id',$this->loan_id)->get()->count()==1;
    }
    public function isHasRepayment(){
        // check whether there is an object of saving statement related to the saving object
        return $this->hasOne('\App\Models\RepaymentSchedule','loan_id','id')->count()>0;
    }
    /** 
     * To get Loan Schedule
     * We need some of the variable such as :
     * 1. Amount of loan
     * 2. Duration of loan (as month)
     * 3. Rate
     */
    public function getSchedule($repaymentStep=false){
        // Check whether the duration of loan does exist
        if( $this->duration > 0 ){
            $schedule = [] ;
            // Get the total step to return back the loan
            $repaymentAmount = ceil( $this->balance / $this->duration ) ;
            $totalInterest = $totalRepayback = $total_amount_tobe_paid = 0 ;            
            for($step = 0 ; $step < $this->duration ; $step++){
                $repaymentDate = \Carbon\Carbon::parse( $this->start_date ) ;
                $repaymentRecord = [
                    'step' => $step + 1 ,
                    'balance' => ceil( $this->balance - ( $step * $repaymentAmount ) ),
                    'amount' => ceil( ( $step >= ( $this->duration - 1 ) ) ? 
                        ( $this->balance - ( $step * $repaymentAmount ) ) : 
                        $repaymentAmount )
                    ,
                    'repayment_date' => \Carbon\Carbon::parse( $repaymentDate->addMonths( $step + 1 ) )->format('Y-m-d'),
                    'rate' => $this->rate ,
                    'interest' =>  ( $this->balance - ( $step * $repaymentAmount ) ) * ( $this->rate / 100 ) ,
                ];
                $repaymentRecord['amount_tobe_paid'] = $repaymentRecord['amount'] + $repaymentRecord['interest'];
                $totalInterest += $repaymentRecord['interest'] ;
                $totalRepayback += $repaymentRecord['amount'] ;
                $total_amount_tobe_paid += $repaymentRecord['amount_tobe_paid'] ;
                $schedule[] = $repaymentRecord ;
                if( $repaymentStep !== false && $repaymentStep == $step ) {
                    return [
                        'schedule' => $schedule ,
                        'repayment_amount' => $repaymentAmount ,
                        'totalInterest' => $totalInterest,
                        'totalRepayback' => $totalRepayback ,
                        'totalAmountTobePaid' => $total_amount_tobe_paid 
                    ];
                }
            }
            return [
                'schedule' => $schedule ,
                'repayment_amount' => $repaymentAmount ,
                'totalInterest' =>  $totalInterest ,
                'totalRepayback' =>  $totalRepayback ,
                'totalAmountTobePaid' =>  $total_amount_tobe_paid 
            ];
        }
        return false ;
    }
    public function getTotalRepayback(){
        $repaymentBack = 0 ;
        foreach( $this->transactions()->where('transaction_id',4)->get() AS $index => $repayment ){
            $repaymentBack += (double) $repayment -> amount ;
        }
        return $repaymentBack ;
    }
    /** 
     * Calculate Interest 
     * This function is used to save an interest
     * It will save into table loan_transactions
    */
    public function calculateInterest(){
        $date = \Carbon\Carbon::now()->format('Y-m-d H:i:s');

        /** 
         * In case, this is the first time of calculate the interest for loan
         * Check the date that the loan has start with the current meeting date 
         **/
        $interest = $this->getInterest();
        $start = \Carbon\Carbon::create( $this->start_date );
        $diffDays = $start->diffInDays( \Carbon\Carbon::now() );

        $transaction = \App\Models\LoanTransaction::create([
            'loan_id' => (int) $this->id ,
            'transaction_id' => (int) 7 ,
            'balance' => $this->getBalance() ,
            'amount' => (float) $diffDays > 0 && $diffDays < \App\Models\Setting::getTotalDaysAsMonth() ? ( $interest / \App\Models\Setting::getTotalDaysAsMonth() ) * $diffDays : $interest ,
            'rate' => (float) $this->rate ,
            'repayment_date' => $date ,
            'created_at' => $date,
            'updated_at' => $date
        ]);
        return $transaction ? true : false ;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function people(){
        return $this->belongsTo('\App\Models\People','people_id','id');
    }
    public function referal()
    {
        return $this->belongsTo('\App\Models\People','reference_member_id','id');
    }
    public function transactions(){
        return $this->hasMany('\App\Models\LoanTransaction','loan_id','id');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function getStartDateAttribute(){
        return \Carbon\Carbon::parse( $this->attributes['start_date'] )->format('Y-m-d H:i:s');
    }
    public function setCodeAttribute($val)
    {
        $this->attributes['code'] = (string) $val ;
    }
    public function setMemberIdDateAttribute($val)
    {
        $this->attributes['people_id'] = (int) $val;
    }
    public function setBalanceDateAttribute($val)
    {
        $this->attributes['balance'] = (double) $val;
    }
    public function setStartDateAttribute($val)
    {
        $this->attributes['start_date'] = \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
    }
    public function setDurationAttribute($val)
    {
        $this->attributes['duration'] = (int) $val;
    }
    public function setMemberIdAttribute($val)
    {
        $this->attributes['people_id'] = (int) $val;
    }
    public function setPdfAttribute($val)
    {
        $this->attributes['pdf'] = (string) $val;
    }
}

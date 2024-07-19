<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanTransaction extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    
    protected $casts = [
        'loan_id' => 'int' ,
        'transaction_id' => 'int' ,
        'amount' => 'double' ,
        'repayment_date' => 'date' ,
        'repayback' => 'double' ,
        'interest' => 'double' ,
        'rate' => 'double'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getBalance(){
        return (float) $this->loan->balance + $this->getTotalSubLoanAmounts() - $this->getTotalRepaybackAmounts();
    }

    public function getTotalRepaybackAmounts(){
        return $this->loan->getRepaybackTransactions()->where('id','<',$this->id)->sum('amount');
    }

    public function getTotalSubLoanAmounts()
    {
        return $this->loan->getSubLoanTransactions()->where('id', '<', $this->id)->sum('amount');
    }

    public function getTotalInterestAmounts()
    {
        return $this->loan->getInterestTransactinos()->where('id', '<', $this->id)->sum('amount');
    }
    /**
     * Get total repayment
     */
    public static function getTotalRepayments($repaymentDate = false)
    {
        return $repaymentDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? static::where('transaction_id', 4)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : static::where('transaction_id', 4)->where('repayment_date', 'LIKE', $repaymentDate . '%')->sum('amount');
    }
    /**
     * Get total repayment
     */
    public static function getTotalInterests($repaymentDate = false)
    {
        return $repaymentDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? static::where('transaction_id', 7)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : static::where('transaction_id', 5)->where('repayment_date', 'LIKE', $repaymentDate . '%')->sum('amount');
    }
    /**
     * Get total sub loan
     */
    public static function getTotalSubLoans($repaymentDate = false)
    {
        return $repaymentDate
            /** Return withdrawal transactions without specific date (month) [YYYY-mm] */
            ? static::where('transaction_id', 5)->sum('amount')
            /** Return withdrawal transactions with specific date (month) [YYYY-mm] */
            : static::where('transaction_id', 5)->where('repayment_date', 'LIKE', $repaymentDate . '%')->sum('amount');
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function type(){
        return $this->hasOne('\App\Models\TransactionType','id','transaction_id');
    }
    public function loan()
    {
        return $this->hasOne('\App\Models\Loan', 'id', 'loan_id');
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
    public function setLoanIdAttribute($val)
    {
        $this->attributes['loan_id'] = (int) $val ;
    }
    public function setTransactionIdAttribute($val)
    {
        $this->attributes['transaction_id'] = (int) $val ;
    }
    public function setAmountAttribute($val)
    {
        $this->attributes['amount'] = (double) $val;
    }
    public function setRepaymentDateAttribute($val)
    {
        $this->attributes['repayment_date'] = \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
    }
    public function setRepaybackAttribute($val)
    {
        $this->attributes['repayback'] = (double) $val;
    }
    public function setInterestAttribute($val)
    {
        $this->attributes['interest'] = (double) $val ;
    }
    public function setRateAttribute($val)
    {
        $this->attributes['rate'] = (double) $val ;
    }
    public function getRepaymentDateAttribute()
    {
        return \Carbon\Carbon::parse( $this->attributes['repayment_date'] )->format('Y-m-d H:i:s');
    }
}

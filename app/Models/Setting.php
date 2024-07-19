<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;
    /** VAT */
    public static function getVat(){
        if ($record = static::where('key', 'VAT')->first() ) {
            return (double) $record->value ;
        } else {
            $record = new Setting();
            $record->key = "VAT";
            $record->name = "Value on TAX";
            $record->description = "Value on TAX";
            $record->value = 0;
            $record->field = "vat";
            $record->active = 1;
            $record->save();
            return (double) $record->value ;
        }
    }
    public static function setVat($val){
        $record = null ;
        if( $record = static::where('key','VAT')->first() ){
            $record->value = (double) $val ;
        }else{
            $record = new Setting();
            $record->key = "VAT" ;
            $record->name = "Value on TAX";
            $record->description = "Value on TAX";
            $record->value = 0 ;
            $record->field = "double";
            $record->active = 1;
            $record->save();
        }
        return $record ;
    }
    /** Saving */
    public static function getSaving()
    {
        if ($record = static::where('key', 'SAVING_METHOD')->first() ) {
            return (int) $record->value ;
        } else {
            $record = new Setting();
            $record->key = "SAVING_METHOD";
            $record->name = "Saving Method";
            $record->description = "Saving Method get 2 values: 0 => monthly , 1 => base on date";
            $record->value = 0;
            $record->field = "int";
            $record->active = 1;
            $record->save();
            return (int) $record->value ;
        }
    }
    public static function setSaving($val)
    {
        $record = null ;
        if ($record = static::where('key', 'SAVING_METHOD')->first() ) {
            $record->value = (int) $val;
        } else {
            $record = new Setting();
            $record->key = "SAVING_METHOD";
            $record->name = "Saving Method";
            $record->description = "Saving Method get 2 values: 0 => manual , 1 => monthly";
            $record->value = 0;
            $record->field = "int";
            $record->active = 1;
            $record->save();
        }
        return $record;
    }
    /** Total days as month */
    public static function getTotalDaysAsMonth()
    {
        if ($record = static::where('key', 'TOTAL_DAYS_PER_MONTH')->first() ) {
            return (int) $record->value ;
        } else {
            $record = new Setting();
            $record->key = "TOTAL_DAYS_PER_MONTH";
            $record->name = "Total days assume as a month";
            $record->description = "The total of days that count as a month";
            $record->value = 30;
            $record->field = "int";
            $record->active = 1;
            $record->save();
            return (int) $record->value ;
        }
    }
    public static function setTotalDaysAsMonth($val)
    {
        $record = null ;
        if ($record = static::where('key', 'TOTAL_DAYS_PER_MONTH')->first() ) {
            $record->value = (int) $val;
        } else {
            $record = new Setting();
            $record->key = "TOTAL_DAYS_PER_MONTH";
            $record->name = "Total days assume as a month";
            $record->description = "The total of days that count as a month";
            $record->value = 30;
            $record->field = "int";
            $record->active = 1;
            $record->save();
        }
        return $record;
    }
    /** Set total days before loan repayment for sending confirmation message to client */
    public static function getLoanNotificationDays()
    {
        if ($record = static::where('key', 'LOAN_NOTIFICATION_DAYS')->first()) {
            return (int) $record->value ;
        } else {
            $record = new Setting();
            $record->key = "LOAN_NOTIFICATION_DAYS";
            $record->name = "Loan repayment ";
            $record->description = "Total days before loan repayment for sending the confirmation of loan repayment";
            $record->value = 5;
            $record->field = "int";
            $record->active = 1;
            $record->save();
            return (int) $record->value ;
        }
    }
    public static function setLoanNotificationDays($val)
    {
        $record = null;
        if ($record = static::where('key', 'LOAN_NOTIFICATION_DAYS')->first()) {
            $record->value = (int) $val ;
        } else {
            $record = new Setting();
            $record->key = "LOAN_NOTIFICATION_DAYS";
            $record->name = "Loan repayment ";
            $record->description = "Total days before loan repayment for sending the confirmation of loan repayment";
            $record->value = 5;
            $record->field = "int";
            $record->active = 1;
            $record->save();
        }
        return $record;
    }
}

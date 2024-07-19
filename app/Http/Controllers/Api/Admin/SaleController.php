<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Mail\MobilePasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use App\Models\Sale\Sale as RecordModel ;
use App\Models\Sale\SaleDetail ;
use App\Http\Controllers\CrudController;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class SaleController extends Controller
{
    private $selectFields = [
        'id',
        'code' ,
        'total' ,
        'grand_total' ,
        'sale_id' ,
        'client_id' ,
        'payment_id' ,
        'discount_id' ,
        'store_id' 
    ];
    

    /**
     * POS Place order
     */
    public function posPlaceOrder(Request $request){
        /**
         * Saving the detail of thes
         */
        /**
         * Generate code for invoice
         */
        $sale = RecordModel::create([
            'code' => $code ,
            'total'  => $total ,
            'grand_total'  => $grandTotal ,
            'sale_id'  => $request->sale_id ,
            'client_id'  => $request->client_id ,
            'payment_id'  => $request->payment_id ,
            'discount_id'  => $request->discount_id ,
            'store_id'  => $request->store_id
        ]);
        $code = "POS" . sprintf("%04d", $record->id) . \Carbon\Carbon::today()->format('Ymd');
        
    }
}

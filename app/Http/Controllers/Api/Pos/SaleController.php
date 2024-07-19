<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use App\Models\Product\Product;
use Illuminate\Http\File;
use App\Models\Sale\Sale as RecordModel;
use App\Models\Sale\SaleDetail;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
  private $selectFields = [
    'id',
    'code' ,
    'total' ,
    'vat' ,
    'discount' ,
    'grand_total' ,
    'saler_id',
    'client_id' ,
    'payment_id' ,
    'discount_id' ,
    'store_id' ,
    'note',
    'table_number'
  ];
  /**
   * Listing function
   */
  public function storeInvoiceByStaff(Request $request){
      $user = \Auth::user() ;
      if( $user == null ){
          return response()->json([
              'message' => 'សូមចូលប្រើម្ដងទៀត។'
          ],401);
      }
      /** Format from query string */
      $search = isset( $request->search ) && $request->serach !== "" ? $request->search : false ;
      $perPage = isset( $request->perPage ) && $request->perPage !== "" ? $request->perPage : 500 ;
      $page = isset( $request->page ) && $request->page !== "" ? $request->page : 1 ;
      $store_id = isset( $request->store_id ) && $request->store_id > 0  ? $request->store_id : false ;
      $client_id = isset( $request->client_id ) && $request->client_id > 0  ? $request->client_id : false ;
      $saler_id = isset( $request->saler_id ) && $request->saler_id > 0  ? $request->saler_id : false ;

      if( !$store_id ){
          return response()->json([
              'message' => 'សូមបញ្ជាក់ ហាងជាមុនសិន។'
          ],403);
      }

      $queryString = [
          "where" => [
              // 'default' => [
              //     [
              //         'field' => 'type_id' ,
              //         'value' => $type === false ? "" : $type
              //     ]
              // ],
              // 'in' => [] ,
              // 'not' => [] ,
              'like' => [
                [
                  'field' => 'created_at' ,
                  'value' => \Carbon\Carbon::now()->format('Y-m-d')
                ]
              ] ,
          ] ,
          "pivots" => [
              // $unit ?
              // [
              //     "relationship" => 'units',
              //     "where" => [
              //         "in" => [
              //             "field" => "id",
              //             "value" => [$request->unit]
              //         ],
              //     // "not"=> [
              //     //     [
              //     //         "field" => 'fieldName' ,
              //     //         "value"=> 'value'
              //     //     ]
              //     // ],
              //     // "like"=>  [
              //     //     [
              //     //        "field"=> 'fieldName' ,
              //     //        "value"=> 'value'
              //     //     ]
              //     // ]
              //     ]
              // ]
              // : []
              $store_id ?
              [
                  "relationship" => 'store',
                  "where" => [
                      "in" => [
                          "field" => "id",
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
                  //        "field"=> 'fieldName' ,
                  //        "value"=> 'value'
                  //     ]
                  // ]
                  ]
              ]
              : [] ,
              $saler_id ?
              [
                  "relationship" => 'saler',
                  "where" => [
                      "in" => [
                          "field" => "id",
                          "value" => [ $saler_id ]
                      ],
                  // "not"=> [
                  //     [
                  //         "field" => 'fieldName' ,
                  //         "value"=> 'value'
                  //     ]
                  // ],
                  // "like"=>  [
                  //     [
                  //        "field"=> 'fieldName' ,
                  //        "value"=> 'value'
                  //     ]
                  // ]
                  ]
              ]
              : [] ,
              // $client_id ?
              // [
              //     "relationship" => 'client',
              //     "where" => [
              //         "in" => [
              //             "field" => "client_id",
              //             "value" => [ $client_id ]
              //         ],
              //     // "not"=> [
              //     //     [
              //     //         "field" => 'fieldName' ,
              //     //         "value"=> 'value'
              //     //     ]
              //     // ],
              //     // "like"=>  [
              //     //     [
              //     //        "field"=> 'fieldName' ,
              //     //        "value"=> 'value'
              //     //     ]
              //     // ]
              //     ]
              // ]
              // : []
          ],
          "pagination" => [
              'perPage' => $perPage,
              'page' => $page
          ],
          "search" => $search === false ? [] : [
              'value' => $search ,
              'fields' => [
                'code'
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
        "store" => ['id','name' , 'location_name' , 'lat_long' , 'address' , 'images' , 'phone' ] ,
        "client" => ['id','image' , 'lastname' , 'firstname' , 'phone' , 'email' , 'avatar_url' ] ,
        "saler" => ['id','image' , 'lastname' , 'firstname' , 'phone' , 'email' , 'avatar_url' ] ,
        "transactions" => [ 'id' , 'sale_id', 'stock_unit_id', 'discount_id', 'discount', 'quantity' , 'unit_price' , 'amount' ]
      ]);

      $builder = $crud->getListBuilder();
      
      /**
       * Filter the record by the stock unit sku
       */
      if( $search ){
        $builder = $builder->whereHas('client',function( $clientQuery ) use( $search ){
                $words = explode(' ', str_replace(',',' ',$search) );
                foreach( $words as $index => $word ) {
                  foreach( ['firstname','lastname','phone','email'] as $fieldIndex => $field ){
                    $clientQuery = $index == 0 && $fieldIndex == 0
                        ? $clientQuery->where($field,'like','%'. $word .'%')
                        : $clientQuery->orWhere($field,'like','%'. $word .'%');
                  }
                }
        });
      }
      $responseData = $crud->pagination(true, $builder);

      $responseData['records'] = $responseData['records']->map(function($record) use( $store_id ) {
        $record['transactions'] = array_map(function($transaction){
          $stockUnit = \App\Models\Stock\StockUnit::find( $transaction->stock_unit_id );
          $unit = \App\Models\Stock\Unit::find( $stockUnit->unit_id );
          $stock = \App\Models\Stock\Stock::find( $stockUnit->stock_id );
          $stock->product ;
          $stock->attributeVariant;
          // \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id', $stock->attributeVariant->variants)->get() ;
          return [
            'id' => $transaction->id ,
            'discount_id' => $transaction->discount_id ,
            'discount' => $transaction->discount ,
            'sale_id' => $transaction->sale_id ,
            'stock_unit_id' => $transaction->stock_unit_id ,
            'quantity' => $transaction->quantity ,
            'unit_price' => $transaction->unit_price ,
            'amount' => $transaction->amount ,
            'stock' => $stock ,
            'unit' => $unit ,
            'variants' => \App\Models\Product\Variant::whereIn( 'id', $stock->attributeVariant->variants )->get()->map(function($variant){ return $variant->name ;})
          ];
        },$record['transactions']);
        return $record;
      });

      $responseData['message'] = __("crud.read.success");
      $responseData['ok'] = true ;
      return response()->json($responseData, 200);
  }
    /**
     * POS Place order
     */
    public function placeOrders(Request $request){
      /**
       * Generate code for invoice
       */
      $sale = RecordModel::create([
          'code' => "" ,
          'total'  => 0.0 ,
          'discount' => 0.0 ,
          'vat' => 0.0 ,
          'grand_total'  => 0.0 ,
          'table_number' => $request->tag['number'] ,
          'saler_id'  => $request->saler_id > 0 ? $request->saler_id : 0 ,
          'client_id'  => $request->client_id > 0 ? $request->client_id : 0 ,
          'payment_id'  => $request->payment_id > 0 ? $request->payment_id : 0 ,
          'discount_id'  => $request->discount_id > 0 ? $request->discount_id : 0 ,
          'store_id'  => $request->store_id > 0 ? $request->store_id : 0
      ]);
            /**
       * Saving the detail of the sale (invoice)
       */
      $orderProducts = isset( $request->items ) && count( $request->items ) > 0 ? $request->items : [] ;
      foreach( $orderProducts AS $index => $orderProduct ){
        $saleDetail = SaleDetail::create([
          'sale_id' => $sale->id , 
          'stock_unit_id' => $orderProduct['stock_unit']['id'], 
          'discount_id' => isset( $orderProduct['discount_id'] ) ? $orderProduct['discount_id'] : 0 , 
          'quantity' => $orderProduct['quantity'] , 
          'unit_price' => $orderProduct['stock_unit']['sale_price'] > 0 ? $orderProduct['stock_unit']['sale_price'] : $orderProduct['stock_unit']['unit_price'] , 
          'amount' => $orderProduct['quantity'] * ( $orderProduct['stock_unit']['sale_price'] > 0 ? $orderProduct['stock_unit']['sale_price'] : $orderProduct['stock_unit']['unit_price'] )
        ]);
      }
      $sale->code = "POS" . sprintf("%04d-%04d-", $request->store_id , $sale->id ) . \Carbon\Carbon::today()->format('Ymd');
      if( $sale->discount_id > 0 ){
        $sale->discount = $sale->getTotalDiscount();
      }
      $sale->vat = $sale->getVatAmount();
      $sale->total = $sale->getTotalAmount();
      $sale->grand_total = $sale->getGrandTotal();
      $sale->save();
      $sale->transactions()->get()->map(function($record){
          $stockUnit = \App\Models\Stock\StockUnit::find( $record['stock_unit_id'] );
          $unit = \App\Models\Stock\Unit::find( $record['unit_id'] );
          $stock = \App\Models\Stock\Stock::find( $stockUnit->stock_id );
          $stock->product ;
          $stock->attributeVariant;
          // \App\Models\Product\Variant::select(['id','name','attribute_id'])->whereIn('id', $stock->attributeVariant->variants)->get() ;
          return [
            'id' => $record->id ,
            'discount_id' => $record->discount_id ,
            'sale_id' => $record->sale_id ,
            'stock_unit_id' => $record->stock_unit_id ,
            'quantity' => $record->quantity ,
            'unit_price' => $record->unit_price ,
            'amount' => $record->amount ,
            'stock' => $stock
          ];
      });
      return response()->json([
        'ok' => true ,
        'record' => $sale ,
        'message' => 'ការបញ្ជាទិញបានបញ្ចប់។'
      ],200);
  }

}


    
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Http\Client\Exception;
use Illuminate\Database\Eloquent\Collection;

class CrudController extends Controller {
    private $model = null ;
    private $fields = ['id', 'created_at', 'updated_at'];
    private $renameFields = [];
    private $fieldsWithCallback = [] ;
    private $request = null ;
    private $relationshipFunctions = [] ;
    private $extraFields = [] ;

    public function __construct($model=false,$request=false,$fields=false,$fieldsWithCallback=false,$renameFields=false,$extraFields=false){
        $model ? $this->setModel($model) : false ;
        $request ? $this->setRequest($request) : false ;
        is_array( $fields ) && !empty( $fields ) ? $this->setFields($fields) : false ;
        is_array( $renameFields ) && !empty( $renameFields ) ? $this->setRenameFields($renameFields) : false ;
        is_array( $fieldsWithCallback ) && !empty( $fieldsWithCallback ) ? $this->setFieldsWithCallback($fieldsWithCallback) : false ;
        is_array( $extraFields ) && !empty( $extraFields ) ? $this->setExtraFields($extraFields) : false ;
    }
    public function setModel($model){
        if (!is_object($model)) throw new Exception("Error execute function : " . __FUNCTION__ . " on class " . __CLASS__, 1);
        $this->model = $model;
    }
    public function getModel(){
        return $this->model;
    }
    public function setRequest($request){
        $this->request = $request;
    }
    public function getRequest(){
        return $this->request;
    }
    public function setFields($fields){
        $this->fields = $fields !== false && is_array($fields) && !empty($fields) ? $fields : $this->fields;
    }
    public function setExtraFields($fields){
        $this->extraFields = $fields !== false && is_array($fields) && !empty($fields) ? $fields : $this->extraFields;
    }
    public function setRenameFields($fields){
        $this->renameFields = $fields !== false && is_array($fields) && !empty($fields) ? $fields : $this->renameFields;
    }
    public function setFieldsWithCallback($fields){
        $this->fieldsWithCallback = $fields !== false && is_array($fields) && !empty($fields) ? $fields : $this->fieldsWithCallback;
    }
    public function getFields(){
        return $this->fields;
    }
    public function setRelationshipFunctions($relationshipFunctions=false){
        $this->relationshipFunctions = $relationshipFunctions !== false && !empty( $relationshipFunctions ) ? $relationshipFunctions : [] ;
    }
    public function getRelationshipFunctions(){
        return $this->relationshipFunctions;
    }
    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getListBuilder($withTrashed=false)
    {
        $query_builder = $this->model->select($this->fields) ;
        if( $withTrashed ) $query_builder = $query_builder->withTrashed();
        /** Start checking filters */
        if (isset($this->request->where) && !empty($this->request->where)) {
            $query_builder = $query_builder->where(function ($query) {
                /** Construct the filter with where conditions */
                if( isset( $this->request->where ) && !empty( $this->request->where ) ){
                    /** Filter with condition where */
                    if( isset( $this->request->where['default'] ) && !empty( $this->request->where['default'] ) ){
                        foreach ($this->request->where['default'] as $index => $condition ) {
                            if( isset( $condition['value'] ) && is_array( $condition['value'] ) && !empty( $condition['value'] ) ){
                                $query = $query->whereIn( $condition['field'] , $condition['value'] );
                            }
                            else if( isset( $condition['value'] ) && !is_array( $condition['value'] ) && $condition['value'] !== '' ){
                                $query = $query->whereIn($condition['field'] , [$condition['value']]);
                            }
                        }
                    }
                    /** Filter with condition where in */
                    if( isset( $this->request->where['in'] ) && !empty( $this->request->where['in'] ) ){
                        foreach ($this->request->where['in'] as $index => $condition ) {
                            if( isset( $condition['value'] ) && is_array( $condition['value'] ) && !empty( $condition['value'] ) ){
                                $query = $query->whereIn( $condition['field'] , $condition['value'] );
                            }
                            else if( isset( $condition['value'] ) && !is_array( $condition['value'] ) && $condition['value'] !== '' ){
                                $query = $query->whereIn($condition['field'] , [$condition['value']]);
                            }
                        }
                    }
                    /** Filter with condition where not */
                    if( isset( $this->request->where['not'] ) && !empty( $this->request->where['not'] ) ){
                        foreach ($this->request->where['not'] as $index => $condition ) {
                            if( isset( $condition['value'] ) && is_array( $condition['value']) && !empty( $condition['value'] ) ){
                                $query = $query->whereNotIn($condition['field'], $condition['value']);
                            }
                            else if( isset( $condition['value'] ) && !is_array( $condition['value'] ) && $condition['value'] !== '' ){
                                $query = $query->whereNotIn($condition['field'], [$condition['value']]);
                            }
                        }
                    }
                    /** Filter with condition where like */
                    if( isset( $this->request->where['like'] ) && !empty( $this->request->where['like'] ) ){
                        foreach ($this->request->where['like'] as $index => $condition ) {
                            if( isset( $condition['value'] ) && $condition['value'] !== "" ){
                                $query = $query->where($condition['field'], "LIKE" , "%".$condition['value']."%");
                            }
                        }
                    }
                }
            });

        }

        /** Start checking filters of pivot table if exist */
        if (isset($this->request->pivots) && !empty($this->request->pivots)) {
            foreach( $this->request->pivots AS $pivot){
                $query_builder = $query_builder->where(function ($query) use($pivot) {
                    /** Construct the filter with where in conditions */
                    if (
                        isset($pivot['relationship']) &&
                        isset($pivot['where']['default']['field']) &&
                        !empty($pivot['where']['default']['field']) &&
                        isset($pivot['where']['default']['value'])
                    ) {
                        $query = $query->whereHas($pivot['relationship'], function ($pivotQuery) use($pivot) {
                            if (is_array($pivot['where']['default']['value']) && !empty($pivot['where']['default']['value'])) {
                                $pivotQuery->where($pivot['where']['default']['field'], $pivot['where']['default']['value']);
                            }else if ( isset($pivot['where']['default']['value'] ) && !is_array($pivot['where']['default']['value']) ) {
                                $pivotQuery->where($pivot['where']['default']['field'], [$pivot['where']['default']['value']] );
                            }
                        });
                    }
                    /** Construct the filter with where in conditions */
                    if (
                        isset($pivot['relationship']) &&
                        isset($pivot['where']['in']['field']) &&
                        !empty($pivot['where']['in']['field']) &&
                        isset($pivot['where']['in']['value'])
                    ) {
                        $query = $query->whereHas($pivot['relationship'], function ($pivotQuery) use($pivot) {
                            if (is_array($pivot['where']['in']['value']) && !empty($pivot['where']['in']['value'])) {
                                $pivotQuery->whereIn($pivot['where']['in']['field'], $pivot['where']['in']['value']);
                            }else if ( isset($pivot['where']['in']['value'] ) && !is_array($pivot['where']['in']['value']) ) {
                                $pivotQuery->whereIn($pivot['where']['in']['field'], [$pivot['where']['in']['value']] );
                            }
                        });
                    }
                    /** Construct the filter with where not in conditions */
                    if (
                        isset($pivot['relationship']) &&
                        isset($pivot['where']['not']['field']) &&
                        !empty($pivot['where']['not']['field']) &&
                        isset($pivot['where']['not']['value']) &&
                        $pivot['where']['not']['value'] > 0
                    ) {
                        $query = $query->whereHas($pivot['relationship'], function ($pivotQuery) use($pivot) {
                            if (is_array($pivot['where']['not']['value']) && !empty($pivot['where']['not']['value'])) {
                                $pivotQuery->whereNot($pivot['where']['not']['field'], $pivot['where']['not']['value']);
                            }else if ( isset($pivot['where']['not']['value'] ) && !is_array($pivot['where']['not']['value']) ) {
                                $pivotQuery->whereNot($pivot['where']['not']['field'], [$pivot['where']['not']['value']] );
                            }
                        });
                    }
                });
            }
        }


        /** Start ordering field */
        if (is_array($this->request->order) && !empty($this->request->order)) {
            if ($this->request->order['by'] == 'asc' || $this->request->order['by'] == 'desc') {
                /** Construct the fields to be sorted */
                $query_builder = $query_builder->orderBy($this->request->order['field'], $this->request->order['by']);
            }
        }
        /** Start searching */
        if (
            /** Check the query string search whether it does exist or not */
            isset($this->request->search) &&
            /** Check the key (value) of array within query string search whether it does exist or not */
            isset($this->request->search['value']) && ($this->request->search['value'] != "") &&
            isset($this->request->search['fields']) && !empty($this->request->search['fields'])
        ) {

                /** Construct the search conditions with provided fields */
                $query_builder = $query_builder->where(function ($query) {
                    /** Check whether fields to be search is/are provided */
                    /** Start to build the search condition */
                    foreach ($this->request->search['fields'] as $fieldIndex => $field){
                        $searchWords = explode(" ",$this->request->search['value']);
                        $fieldIndex > 0
                            ? $query->orWhere(function($query) use ($field, $searchWords) {
                                    foreach( $searchWords AS $wordIndex => $word ){
                                        if(strlen($word)<=0)continue;
                                        /**
                                         * Use to get all records that contains one or more of string search
                                         */
                                        // $wordIndex > 0 
                                        // ? $query->orWhere($field, 'LIKE', "%" . $word . "%")
                                        // : $query->where($field, 'LIKE', "%" . $word . "%");
                                        /**
                                         * Use to get all records that matched all conditions
                                         */
                                        $query->where($field, 'LIKE', "%" . $word . "%");
                                    }
                                })
                            : $query->where(function($query) use ($field, $searchWords) {
                                foreach( $searchWords AS $wordIndex => $word ){
                                    if(strlen($word)<=0)continue;
                                        /**
                                         * Use to get all records that contains one or more of string search
                                         */
                                        // $wordIndex > 0 
                                        // ? $query->orWhere($field, 'LIKE', "%" . $word . "%")
                                        // : $query->where($field, 'LIKE', "%" . $word . "%");
                                        /**
                                         * Use to get all records that matched all conditions
                                         */
                                        $query->where($field, 'LIKE', "%" . $word . "%");
                                }
                            });
                    }

                    if( isset( $this->request->pivots) && !empty( $this->request->pivots) ){
                        foreach( $this->request->pivots AS $pivot){
                            if (
                                isset($pivot['relationship']) &&
                                isset($pivot['where']['like']) && !empty($pivot['where']['like'])
                            ) {
                                $query = $query->orWhereHas($pivot['relationship'], function ($pivotQuery) use($pivot) {
                                    /** Construct the filter with like condition */
                                    /** Start searching */
                                    foreach ($pivot['where']['like'] as $pivotConditionIndex => $pivotCondition ) {
                                        $searchWords = explode(" ", $pivotCondition['value']);
                                        $pivotConditionIndex > 0
                                        ? $pivotQuery->orWhere(function ($query) use ($pivotCondition, $searchWords) {
                                            foreach ($searchWords as $wordIndex => $word) {
                                                /**
                                                 * Use to get all records that contains one or more of string search
                                                 */
                                                // $wordIndex > 0 
                                                // ? $query->orWhere($field, 'LIKE', "%" . $word . "%")
                                                // : $query->where($field, 'LIKE', "%" . $word . "%");
                                                /**
                                                 * Use to get all records that matched all conditions
                                                 */
                                                $query->where($pivotCondition['field'], 'LIKE', "%" . $word . "%");
                                            }
                                        })
                                        : $pivotQuery->where(function ($query) use ($pivotCondition, $searchWords) {
                                            foreach ($searchWords as $wordIndex => $word) {
                                                /**
                                                 * Use to get all records that contains one or more of string search
                                                 */
                                                // $wordIndex > 0 
                                                // ? $query->orWhere($field, 'LIKE', "%" . $word . "%")
                                                // : $query->where($field, 'LIKE', "%" . $word . "%");
                                                /**
                                                 * Use to get all records that matched all conditions
                                                 */
                                                $query->where($pivotCondition['field'], 'LIKE', "%" . $word . "%");
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                });

        }
        return $query_builder;
    }

    public function getRecords($formatRecord=false, $fieldsWithCallback = false){
        return $formatRecord
            ? $this->formatRecords( $this->getListBuilder()->get() )
            : $this->getListBuilder()->get() ;
    }

    public function pagination($formatRecord = false, $query_builder = false){
        // $query_builder = $query_builder !== false ? $query_builder : $this->getListBuilder() ;
        $query_builder = $query_builder !== false ? $query_builder : $this->getListBuilder();
        /** Get the page variable for pagination */
        $perPage = (int) (isset($this->request->pagination['perPage']) && $this->request->pagination['perPage'] != "" ? $this->request->pagination['perPage'] : 20);
        $page = (int) (isset($this->request->pagination['page']) && $this->request->pagination['page'] != "" ? $this->request->pagination['page'] : 1);
        $totalRecords = $query_builder->count();
        $totalPages = ceil($totalRecords / $perPage);
        $first = $totalPages <= 0 || $page <= 1 ? null : 1;
        $previous = $totalPages <= 0 || $page <= 1 ? null : (($page - 1) <= 0 ? null : $page - 1);
        $next = $totalPages <= 0 || $page == $totalPages ? null : (($page + 1) > $totalPages ? null : $page + 1);
        $last = $totalPages <= 0 || $page >= $totalPages ? null : $totalPages;
        /** limit the number record(s) to retrive from database table */
        $query_builder = $query_builder->limit($perPage)
            /** Tell the query builder from which offset/index to start */
            ->offset($perPage * ($page - 1));
        /** Generate Site URL */

        $firstUrl = $previousUrl = $nextUrl = $lastUrl = null;
        $input = $this->request->input();
        if ($first > 0) {
            $input['pagination']['page'] = $first;
            $this->request->merge($input);
            $firstUrl = $this->request->fullUrl();
        }
        if ($previous > 0) {
            $input['pagination']['page'] = $previous;
            $this->request->merge($input);
            $previousUrl = $this->request->fullUrl();
        }
        if ($next > 0) {
            $input['pagination']['page'] = $next;
            $this->request->merge($input);
            $nextUrl = $this->request->fullUrl();
        }
        if ($last > 0) {
            $input['pagination']['page'] = $last;
            $this->request->merge($input);
            $lastUrl = $this->request->fullUrl();
        }

        $pagination = [
            "totalRecords" => $totalRecords,
            "totalPages" => $totalPages,
            "perPage" => $perPage,
            "first" => $first,
            "firstUrl" =>  $firstUrl,
            "previous" => $previous,
            "previousUrl" => $previousUrl,
            "page" => $page,
            "next" => $next,
            "nextUrl" => $nextUrl,
            "last" => $last,
            "lastUrl" => $lastUrl
        ];

        return [
            'pagination' => $pagination ,
            'records' => $formatRecord
                ? $this->formatRecords($query_builder->get())
                : $query_builder->get()
        ];
    }

    /** Map Photo */
    public function formatRecords(Collection $collection)
    {
        return $collection->map(function ($record) {
            return $this->formatRecord($record);
        });
    }
    public function formatRecord($record){
        // if (isset($record->photos) && is_array($record->photos) ){
        //     $photos = [];
        //     foreach ($record->photos as $index => $photo) {
        //         if ($photo != null && Storage::disk(env('STORAGE_DRIVER','public'))->exists($photo)) {
        //             $photos[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($photo);
        //         }
        //     }
        //     $record->photos = $photos;
        // }
        // else{
        //     if( isset($record->photos ) ) $record->photos = [] ;
        // }
        // $pdfs = [];
        // if (isset($record->pdfs) && is_array($record->pdfs)) {
        //     foreach ($record->pdfs as $index => $pdf) {
        //         if ($pdf != null && Storage::disk(env('STORAGE_DRIVER','public'))->exists($pdf)) {
        //             $pdfs[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($pdf);
        //         }
        //     }
        // } else if( isset($record->pdfs) && !is_array($record->pdfs) ) {
        //     if (Storage::disk(env('STORAGE_DRIVER','public'))->exists($record->pdfs)) {
        //         $pdfs[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($record->pdfs);
        //     }else{
        //         $pdfs=[];
        //     }
        // }
        // $record->pdfs = $pdfs;
        // /** In case, record has field avatar_url */
        // if (isset($record->avatar_url ) && $record->avatar_url !== "" ) {
        //     if (Storage::disk(env('STORAGE_DRIVER','public'))->exists($record->avatar_url)) {
        //         $record->avatar_url = Storage::disk(env('STORAGE_DRIVER','public'))->url($record->avatar_url);
        //     } else {
        //         $record->avatar_url = null;
        //     }
        // } else {
        //     if (isset($record->avatar_url)) $record->avatar_url = null ;
        // }
        /** Construct format of the record */
        $customRecord = [];
        if (isset( $record ) && !empty($this->fields)) {
            foreach ($this->fields as $key => $field){
                $customRecord[ $field ] = 
                    $this->fieldsWithCallback !== false && 
                    is_array( $this->fieldsWithCallback ) && 
                    !empty( $this->fieldsWithCallback ) && 
                    array_key_exists( $field , $this->fieldsWithCallback ) ? 
                        $this->fieldsWithCallback[$field]($record) : 
                        $record->$field ;
                if( is_array( $this->renameFields ) && !empty( $this->renameFields ) && array_key_exists( $field , $this->renameFields ) ){
                    $customRecord[ $this->renameFields[$field] ] = $customRecord[ $field ] ;
                    unset( $customRecord[ $field ] );
                }
            }
        }
        if (isset( $record ) && is_array( $this->extraFields ) && !empty( $this->extraFields ) ) {
            foreach( $this->extraFields as $field => $func )$customRecord[ $field ] = $func( $record );
        }
        /** Call the relationship functions */
        if (isset($record) &&  !empty($this->relationshipFunctions)) {
            foreach ($this->relationshipFunctions as $functionName => $fields ) {
                /** Check whether the relationship is null */
                if( $record->$functionName === null ) continue;
                if( !empty($fields) ){
                    /** if the relationship function return collection */
                    if ($record->$functionName instanceof \Illuminate\Database\Eloquent\Collection) {
                        foreach( $record->$functionName AS $index => $recordOfFunctionCollection ){
                            $tempRecord = new \StdClass;
                            foreach ($fields AS $field ) {
                                 $tempRecord->$field = $recordOfFunctionCollection->$field;
                            }
                            $customRecord[$functionName][] = $tempRecord;
                        }
                    }
                    /** if the relationship function return object */
                    else{
                        foreach ($fields AS $field ) {
                            $customRecord[$functionName][$field] = $record->$functionName->$field;
                        }
                    }
                }else{
                    $customRecord[$functionName] = $record->$functionName;
                }
            }
        }
        if( in_array('id',$this->fields) === false ) $customRecord['id'] = $record->id;

        if(isset($record->document_counts)){
            $customRecord['document_counts'] = $record->document_counts;
        }
        if(isset($record->already_in_folder)){
            $customRecord['already_in_folder'] =  $record->already_in_folder > 0 ? true: false;
        }
        return $customRecord;
    }

    /**
     * Get record from this Control model with specific fields
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function compactList()
    {
        return $this->getListBuilder()->where('active',1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        if( $record = $this->model::create($this->request->except(['id','files'])) ) return $record;
        return false;
    }
    /***
     * Read
     */
    public function read($id=false)
    {
        if ( ( $record = $this->model->find( $id !== false ? $id : $this->request->id ) ) !== null ) return $record;
        return false ;
    }
    /**
     * Update
     */
    public function update($exceptFields=[])
    {
        $data = $this->request->except(isset($exceptFields) && !empty($exceptFields) ? $exceptFields : ['id']) ;
        if ($record = $this->model->where('id', $this->request->id)->update($data)) return $record;
        return false;
    }
    /**
     * Delete
     */
    public function delete()
    {
        if ($this->model->destroy($this->request->id))return true ;
        return false ;
    }
    /** Delete many records */
    public function deletes()
    {
        if($this->model->destroy($this->request->ids)) return true;
        return false;
    }
    /**
     * Force Delete : delete a deleted record permanently
     */
    public function forceDelete(){
        if ($this->model->find($this->request->id) && $this->model->find($this->request->id)->forceDelete() )return true;
        return false;
    }
    /**
     * Force Deletes : delete all deleted records permanently
     */
    public function forceDeletes()
    {
        $this->model->history()->forceDelete();
    }


    /**
     * Restore the record back a record [, in case its model use SoftDeletes ]
     */
    public function restore()
    {
        if ( $this->model->onlyTrashed()->where('id',$this->request->id)->first() && $this->model->onlyTrashed()->where('id', $this->request->id)->first()->restore() ) return true;
        return false;
    }
    /**
     * Restore all records back [, in case its model use SoftDeletes ]
     */
    public function restoreAll()
    {
        $this->model->onlyTrashed()->restore();
    }
    /** Upload file */
    /**
     * @param   $field_name : table field that is work with file / photo
     *          $path : path to store the file
     *          $file : file to be stored
     *          $keepExisting : true -> keep the existing file , false delete existing file and save the new one
     */
    public function upload($field_name,$path,$file, $fileName=false, $keepExisting=true)
    {
        if (($record = $this->model->find($this->request->id))!==null) {
            /**
             * Check the files and delete them
             */
            $files = is_array( $record->$field_name ) ? ( !empty( $record->$field_name ) ? $record->$field_name : [] ) : ( $record->$field_name !== "" ? [ $record->$field_name ] : [] ) ;
            if( !$keepExisting ){
                /**
                 * Delete all the existing files
                 */
                Storage::disk(env('STORAGE_DRIVER','public'))->delete( $files );
                $files = [] ;
            }
            /**
             * Save new file
             */
            $uniqeName = '' ;
            if($fileName !== false ){
                $uniqeName = Storage::disk(env('STORAGE_DRIVER','public'))->putFileAs($path, new File($file), $fileName , 'public');
            }else{
                $uniqeName = Storage::disk(env('STORAGE_DRIVER','public'))->putFile($path, new File($file), 'public');
            }
            $files[] = $uniqeName;
            $record->$field_name = $files;
            $record->save();
            if (is_array($record->$field_name) && count($record->$field_name)) {
                $files = [];
                foreach ($record->$field_name as $index => $file) {
                    if (Storage::disk(env('STORAGE_DRIVER','public'))->exists($file)) {
                        $files[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($file);
                    }
                }
                $record->$field_name = $files;
            }
            return $record;
        }
        return false;
    }
    /** Remove photo */
    public function removeFile($field='photos')
    {
        $record = $this->model->find( $this->request->id );
        if ($record !== null ) {
            if( $this->request->index > -1 && isset( $record->$field ) ){
                $files = $urls = [];
                foreach ($record->$field as $index => $file ) {
                    if ($this->request->index != $index){
                        $files[] = $file ;
                        if (Storage::disk(env('STORAGE_DRIVER','public'))->exists($file)) {
                            $urls[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($file);
                        }
                    }
                    else{
                        Storage::disk(env('STORAGE_DRIVER','public'))->delete($file);
                    }
                }
                $record->$field = $files;
                $record->save();
                $record->$field = $urls;
                return $record ;
            }else{
                $record->$field = [] ;
                $record->save();
                return $record ;
            }
        }
        return false ;
    }
    /** Remove photo */
    public function setThumbnail($field_name)
    {
        if (($record = $this->model->where('id',$this->request->id)->first()) && $this->request->index > -1) {
            $files = [];
            foreach ($record->$field_name as $index => $file) {
                ($this->request->index == $index) ? array_unshift($files, $file) : $files[] = $file;
            }
            $record->$field_name = $files;
            $record->save();
            if (is_array($record->$field_name) && count($record->$field_name) > 0) {
                $files = [];
                foreach ($record->$field_name as $index => $file) {
                    if (Storage::disk(env('STORAGE_DRIVER','public'))->exists($file)) {
                        $files[] = Storage::disk(env('STORAGE_DRIVER','public'))->url($file);
                    }
                }
                $record->$field_name = $files;
            }
            return $record;
        }
        return false;
    }
    /**
     * Boolean field
     * Set true or failed
     */
    public function booleanField($field_name,$booleanVal){
        if ($record = $this->model->where('id',$this->request->id)->update([$field_name=>$booleanVal])) return true;
        return false;
    }
    /** Check exists base on fields, which fields is array type */
    /**
     *
     * @param : $array_fields
     * @param : $shifter 
     * @return: object , false
     */
    public function exists($array_fields,$shifter=false){
        if( isset($array_fields ) && !empty($array_fields ) ){
            $this->model = $this->model->where(function ($query) use ($array_fields) {
                foreach ($array_fields as $index => $field) {
                    $query->where($field, $this->request->$field);
                }
            });
            return $this->model->count() ? $this->model->first() : false ;
        }
        return false ;
    }
}
                
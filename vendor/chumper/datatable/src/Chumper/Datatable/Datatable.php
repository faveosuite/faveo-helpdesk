<?php namespace Chumper\Datatable;

use Chumper\Datatable\Engines\CollectionEngine;
use Chumper\Datatable\Engines\QueryEngine;
use Input;

/**
 * Class Datatable
 * @package Chumper\Datatable
 */
class Datatable {

    /**
     * @param $query
     * @return QueryEngine
     */
    public function query($query)
    {
        return new QueryEngine($query);
    }

    /**
     * @param $collection
     * @return CollectionEngine
     */
    public function collection($collection)
    {
        return new CollectionEngine($collection);
    }

    /**
     * @return Table
     */
    public function table()
    {
        return new Table;
    }

    /**
     * @return bool True if the plugin should handle this request, false otherwise
     */
    public function shouldHandle()
    {
        $echo = Input::get('sEcho',null);
        if(/*Request::ajax() && */!is_null($echo) && is_numeric($echo))
        {
            return true;
        }
        return false;
    }
}

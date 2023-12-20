<?php namespace Chumper\Datatable\Engines;

use Illuminate\Support\Collection;

/**
 * This handles the collections,
 * it needs to compile first, so we wait for the make command and then
 * do all the operations
 *
 * Class CollectionEngine
 * @package Chumper\Datatable\Engines
 */
class CollectionEngine extends BaseEngine {

    /**
     * @var \Illuminate\Support\Collection
     */
    private $workingCollection;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $collection;

    /**
     * @var string
     */
    const OR_CONDITION = 'OR';

    /**
     * @var string
     */
    const AND_CONDITION = 'AND';

    /**
     * @var array Different options
     */
    private $options = array(
        'stripOrder'        =>  false,
        'stripSearch'       =>  false,
        'caseSensitive'     =>  false,
    );

    /**
     * @param Collection $collection
     */
    function __construct(Collection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->workingCollection = $collection;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->workingCollection->count();
    }

    /**
     * @return int
     */
    public function totalCount()
    {
        return $this->collection->count();
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $this->handleInputs();
        $this->compileArray($this->columns);
        $this->doInternalSearch(new Collection(), array());
        $this->doInternalOrder();

        return array_values($this->workingCollection
            ->slice($this->skip,$this->limit)
            ->toArray()
        );
    }

    /**
     * Resets all operations performed on the collection
     */
    public function reset()
    {
        $this->workingCollection = $this->collection;
        return $this;
    }

    public function stripSearch()
    {
        $this->options['stripSearch'] = true;
        return $this;
    }

    public function stripOrder()
    {
        $this->options['stripOrder'] = true;
        return $this;
    }

    public function setSearchStrip()
    {
        $this->options['stripSearch'] = true;
        return $this;
    }

    public function setOrderStrip()
    {
        $this->options['stripOrder'] = true;
        return $this;
    }

    public function setCaseSensitive($value)
    {
        $this->options['caseSensitive'] = $value;
        return $this;
    }

    public function getOption($value)
    {
        return $this->options[$value];
    }
    //--------------PRIVATE FUNCTIONS-----------------

    protected function internalMake(Collection $columns, array $searchColumns = array())
    {
        $this->compileArray($columns);
        $this->doInternalSearch($columns, $searchColumns);
        $this->doInternalOrder();

        return $this->workingCollection->slice($this->skip,$this->limit)->values();
    }

    private function doInternalSearch(Collection $columns, array $searchColumns)
    {
        if((is_null($this->search) || empty($this->search)) && empty($this->fieldSearches))
            return;

        $value = $this->search;
        $caseSensitive = $this->options['caseSensitive'];

        $toSearch = array();

        $searchType = self::AND_CONDITION;

        // Map the searchColumns to the real columns
        $ii = 0;
        foreach($columns as $i => $col)
        {
            if(in_array($columns->get($i)->getName(), $searchColumns) || in_array($columns->get($i)->getName(), $this->fieldSearches))
            {
                // map values to columns, where there is no value use the global value
                if(($field = array_search($columns->get($i)->getName(), $this->fieldSearches)) !== FALSE)
                {
                    $toSearch[$ii] = $this->columnSearches[$field];
                }
                else
                {
                    if($value)
                        $searchType = self::OR_CONDITION;

                    $toSearch[$ii] = $value;
                }
            }
            $ii++;
        }

        $self = $this;
        $this->workingCollection = $this->workingCollection->filter(function($row) use ($toSearch, $caseSensitive, $self, $searchType)
        {

            for($i=0, $stack=array(), $nb=count($row); $i<$nb; $i++)
            {
                if(!array_key_exists($i, $toSearch))
                    continue;

                $column = $i;
                if($self->getAliasMapping())
                {
                    $column = $self->getNameByIndex($i);
                }

                if($self->getOption('stripSearch'))
                {
                    $search = strip_tags($row[$column]);
                }
                else
                {
                    $search = $row[$column];
                }
                if($caseSensitive)
                {
                    if($self->exactWordSearch)
                    {
                        if($toSearch[$i] === $search)
                            $stack[$i] = true;
                    }
                    else
                    {
                        if(str_contains($search,$toSearch[$i]))
                            $stack[$i] = true;
                    }
                }
                else
                {
                    if($self->getExactWordSearch())
                    {
                        if(mb_strtolower($toSearch[$i]) === mb_strtolower($search))
                            $stack[$i] = true;
                    }
                    else
                    {
                        if(str_contains(mb_strtolower($search),mb_strtolower($toSearch[$i])))
                            $stack[$i] = true;
                    }
                }
            }

            if($searchType == $self::AND_CONDITION)
            {
                $result = array_diff_key(array_filter($toSearch), $stack);

                if(empty($result))
                    return true;
            }
            else
            {
                if(!empty($stack))
                    return true;
            }
        });
    }

    private function doInternalOrder()
    {
        if(is_null($this->orderColumn))
            return;

        // Bug added on pull request #309
        $column = array_values($this->orderColumn)[0];
        $direction = array_values($this->orderDirection)[0];
        $stripOrder = $this->options['stripOrder'];

        $sortFunction = 'sortBy';
        if ($direction == BaseEngine::ORDER_DESC)
            $sortFunction = 'sortByDesc';

        $this->workingCollection->{$sortFunction}(function($row) use ($column,$stripOrder) {

            if($this->getAliasMapping())
            {
                $column = $this->getNameByIndex($column[0]);
                return $row[$column];
            }
            if($stripOrder)
            {
                return strip_tags($row[$column]);
            }
            else
            {
                if (is_array($column))
                    return $row[$column[0]];
                return $row[$column];
            }
        });
    }

    private function compileArray($columns)
    {
        $self = $this;
        $this->workingCollection = $this->collection->map(function($row) use ($columns, $self) {
            $entry = array();

            // add class and id if needed
            if(!is_null($self->getRowClass()) && is_callable($self->getRowClass()))
            {
                $entry['DT_RowClass'] = call_user_func($self->getRowClass(),$row);
            }
            if(!is_null($self->getRowId()) && is_callable($self->getRowId()))
            {
                $entry['DT_RowId'] = call_user_func($self->getRowId(),$row);
            }
            $i=0;
            foreach ($columns as $col)
            {
                if($self->getAliasMapping())
                {
                    $entry[$col->getName()] =  $col->run($row);
                }
                else
                {
                    $entry[$i] =  $col->run($row);
                }

                $i++;
            }
            return $entry;
        });
    }
}

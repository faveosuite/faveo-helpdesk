<?php namespace Chumper\Datatable;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

/**
 * Class Table
 * @package Chumper\Datatable
 */
class Table {

    /**
     * @var array
     */
    private $config = array();

    /**
     * @var array
     */
    private $columns = array();

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var array
     */
    private $callbacks = array();

    /**
     * Values to be sent to custom templates
     *
     * @var array
     */
    private $customValues = array();

    /**
     * @var array
     */
    private $data = array();

    /**
     * @var boolean Determines if the template should echo the javascript
     */
    private $noScript = false;

    /**
     * @var String The name of the id the table will have later
     */
    protected $idName;

    /**
     * @var String The name of the class the table will have later
     */
    protected $className;

    /**
     * @var String The footer's display mode
     */
    protected $footerMode = 'hidden';

    /**
     * @var String The view used to render the table
     */
    protected $table_view;

    /**
     * @var String The view used to render the javascript
     */
    protected $script_view;

    /**
     * @var boolean indicates if the mapping was already added to the options
     */
    private $createdMapping = true;

    /**
     * @var array name of mapped columns
     */
    private $aliasColumns = array();

    function __construct()
    {
        $this->config = Config::get('datatable::table');

        $this->setId( $this->config['id'] );
        $this->setClass( $this->config['class'] );
        $this->setOptions( $this->config['options'] );
        $this->setCallbacks( $this->config['callbacks'] );

        $this->noScript = $this->config['noScript'];
        $this->table_view = $this->config['table_view'];
        $this->script_view = $this->config['script_view'];
    }


    /**
     * @return $this
     */
    public function addColumn()
    {
        foreach (func_get_args() as $title)
        {
            if(is_array($title))
            {
                foreach ($title as $mapping => $arrayTitle)
                {
                    $this->columns[] = $arrayTitle;
                    $this->aliasColumns[] = $mapping;
                    if(is_string($mapping))
                    {
                        $this->createdMapping = false;
                    }
                }
            }
            else
            {
                $this->columns[] = $title;
                $this->aliasColumns[] = count($this->aliasColumns)+1;
            }
        }
        return $this;
    }

    /**
     * Count the number of columns in the datatable.
     * @return int
     */
    public function countColumns()
    {
        return count($this->columns);
    }

    /**
     * Remove an option item from the options array
     *
     * @param string $key the name of the key to remove from the options.
     * @return $this
     */
    public function removeOption($key)
    {
        if(isset($this->options[$key])) unset($this->options[$key]);
        return $this;
    }

    /**
     * Set a single option or an array of options for the jquery call
     *
     * @return $this
     * @throws \Exception
     */
    public function setOptions()
    {
        if(func_num_args() == 2)
        {
           $this->options[func_get_arg(0)] =func_get_arg(1);
        }
        else if(func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            foreach (func_get_arg(0) as $key => $option)
            {
                $this->options[$key] = $option;
            }
        }
        else
            throw new Exception('Invalid number of options provided for the method "setOptions"');
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setOrder($order = array())
    {
        $_orders = array();
        foreach ($order as $number => $sort)
        {
            $_orders[] .= '[ ' . $number . ', "' . $sort . '" ]';
        }

        $_build = '[' . implode(', ', $_orders) . ']';

        $this->callbacks['aaSorting'] = $_build;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setCallbacks()
    {
        if(func_num_args() == 2)
        {
            $this->callbacks[func_get_arg(0)] = func_get_arg(1);
        }
        else if(func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            foreach (func_get_arg(0) as $key => $value)
            {
                $this->callbacks[$key] = $value;
            }
        }
        else
            throw new Exception('Invalid number of callbacks provided for the method "setCallbacks"');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setCustomValues()
    {
        if(func_num_args() == 2)
        {
            $this->customValues[func_get_arg(0)] = func_get_arg(1);
        }
        else if(func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            foreach (func_get_arg(0) as $key => $value)
            {
                $this->customValues[$key] = $value;
            }
        }
        else
            throw new Exception('Invalid number of custom values provided for the method "setCustomValues"');

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->options['sAjaxSource'] = $url;
        $this->options['bServerSide'] = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     * @return array
     */
    public function getCustomValues()
    {
        return $this->customValues;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $view
     * @param array $additional_template_variables
     * @return mixed
     */
    public function render($view = null, array $additional_template_variables = null)
    {
        if( ! is_null($view))
            $this->table_view = $view;

	        //If there is an ajax option (new mode since datatable 1.10), do not use compatibility mode (Bruno de l'Escaille)
     	if(!isset($this->options['sAjaxSource']) && !isset($this->options['ajax']))
        {
            $this->setUrl(Request::url());
        }

        // create mapping for frontend
        if(!$this->createdMapping)
        {
            $this->createMapping();
        }

        $template_variables = array (
            'options'   => $this->options,
            'callbacks' => $this->callbacks,
            'values'    => $this->customValues,
            'data'      => $this->data,
            'columns'   => array_combine($this->aliasColumns,$this->columns),
            'noScript'  => $this->noScript,
            'id'        => $this->idName,
            'class'     => $this->className,
            'footerMode'=> $this->footerMode,
        );

        if (is_array($additional_template_variables)) {
            $template_variables += $additional_template_variables;
        }

        return View::make($this->table_view, $template_variables);
    }

    /**
     * Instructs the table not to echo the javascript
     *
     * @return $this
     */
    public function noScript()
    {
        $this->noScript = true;
        return $this;
    }

    /**
     * @param null $view
     * @return mixed
     */
    public function script($view = null)
    {
        if( ! is_null($view))
            $this->script_view = $view;

        // create mapping for frontend
        if(!$this->createdMapping)
        {
            $this->createMapping();
        }

        return View::make($this->script_view,array(
            'options'   =>  $this->options,
            'callbacks' =>  $this->callbacks,
            'id'        =>  $this->idName,
        ));
    }

    /**
     * @return String
     */
    public function getId()
    {
        return $this->idName;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id = '')
    {
        $this->idName = empty($id)? str_random(8) : $id;
        return $this;
    }

    /**
     * @return String
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * Set the name of the class that will be used by the datatable.
     *
     * @param $class the name of the class
     * @return $this
     */
    public function setClass($class)
    {
        $this->className = $class;
        return $this;
    }

    /**
     * Set the footer display mode.
     *
     * @param $value the one of next values: 'hidden', 'columns', 'empty'
     * @return $this
     */
    public function showFooter($value = 'columns')
    {
        $this->footerMode = $value;
        return $this;
    }

    /**
     * Advise the Datatable to return the data mapped with the column name.
     *
     * @param bool $value explicitly set if the table should be aliased or not
     * @return $this
     */
    public function setAliasMapping($value = true)
    {
        $this->createdMapping = !$value;
        return $this;
    }

    //--------------------PRIVATE FUNCTIONS

    /**
     * @return array
     */
    private function createMapping()
    {
        // set options for better handling
        // merge with existing options
        if (!array_key_exists('aoColumns', $this->options)) {
            $this->options['aoColumns'] = array();
        }

        $matching = array();
        $i = 0;

        foreach ($this->aliasColumns as $name) {
            if (array_key_exists($i, $this->options['aoColumns'])) {
                $this->options['aoColumns'][$i] = array_merge_recursive($this->options['aoColumns'][$i], array('mData' => $name));
            } else {
                $this->options['aoColumns'][$i] = array('mData' => $name);
            }
            $i++;
        }

        $this->createdMapping = true;

        return $matching;
    }
}

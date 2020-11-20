<?php


namespace WeDevs\ERP\Accounting\Includes\Classes;


class RequestHandler
{

    protected $request ;
    protected $all ;


    public function __construct( $data )
    {
        $this->request = $data ;
        $this->setData();
    }



    /**
     * set  data as this class property
     */
    protected function setData()
    {
        foreach ($this->request as $key => $value) {
            $this->{$key} = $value;
            $this->all[$key] = $value;
        }

    }


    /**
     * @return mixed
     */
    public function all(){
        return $this->all ;
    }

    /**
     * @param $property
     * @return | null
     */
    public function __get($property)
    {
        return isset($this->{$property}) ? $this->{$property} : null;
    }


    /**
     * @param $name
     * @return bool
     */
    public function __isset($name )
    {
        return  isset($this->{$property}[$name]) ? $this->{$property}[$name] : null;
    }

}

<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Set{
    
    private string $set = '';
    private array  $set_values  = [];
    private array  $set_columns = [];
    
    /**
     * @param array $set Pairs of item => value
     *
     * @return \Safronik\DB\Extensions\QueryBuilder\Update|Set
     */
    public function set( $set ): self
    {
        $set_string = [];
        foreach( $set as $item => $value ){
            $set_string[]                             = ":table.:set_column_$item = :set_value_$item";
            $this->set_columns[ ":set_column_$item" ] = [ $item, 'column_name' ];
            $this->set_values[  ":set_value_$item" ]  = $value;
        }
        
        $this->set = implode( ',', $set_string );
        
        return $this;
    }
}
<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Values{
    
    private string $values      = '';
    private array  $values_data = [];

    /**
     * Appends values to insert
     *
     * @param array $values
     *
     * @return $this
     */
    public function values( array $values ): static
    {
        // Append request string part
        $this->values .= $this->values ? ',' : ''; // prepend ',' if it's first values given
        $this->values .= '(' . trim( str_repeat( '?,',  count( $values ) ), ',' ) . ')';
        
        // Append values
        $this->values_data = array_merge( $this->values_data, array_values( $values ) );
        
        return $this;
    }

    /**
     * Appends values to insert in bulk
     *
     * @param array $data
     * @return $this
     */
    public function valuesBulk( array $data ): static
    {
        foreach( $data as $datum){
            $this->values( $datum );
        }

        return $this;
    }
}
<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Limit{
    
    private string $limit = '';
    private int   $offset = 0;
    private int   $amount = 1000;
    
    /**
     * Compiles limit block
     *
     * @param ?int $amount
     * @param ?int $offset
     *
     * @return $this
     */
    public function limit( int $amount = null, int $offset = null ): static
    {
        if( ! $amount && ! $offset ){
            $this->limit = '';
            
            return $this;
        }
        
        // @todo fix the issue when offset is NULL and amount is INT
        $this->limit = 'LIMIT ' . ( $offset ?? $this->offset ) . ',' . ( $amount ?? $this->amount );
        
        return $this;
    }
}
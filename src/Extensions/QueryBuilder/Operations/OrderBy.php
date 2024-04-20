<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\Exceptions\DBException;

trait OrderBy{
    
    private string $order_by = '';
    private array  $order_by_data = [];
    
    /**
     * Compiles an order by block
     *
     * @param string $column
     * @param string $order
     *
     * @throws \Exception
     *
     * @return static
     */
    public function orderBy( string $column, string $order = 'desc', string $table = null ): static
    {
        if( $column ){
            
            $table = $table ?? $this->getFirstTablePlaceholder();
            
            $order = strtolower( $order );
            ! in_array( $order, ['desc', 'asc'], true ) &&
                throw new DBException('Order is not supported');
            
            $order          = strtoupper( $order );
            
            $this->order_by = "ORDER BY $table.:order_by_$column $order";
            $this->order_by_data[ ":order_by_$column" ] = [ $column, 'column_name'];
        }
        
        return $this;
    }
}
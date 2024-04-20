<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Table{
    
    private string $table      = '';
    private array  $table_data = [];
    
    /**
     * Set table to query from\to
     *
     * @param string $table
     *
     * @return self
     */
    public function table( string $table ): self
    {
        $table_placeholder = ':table_' . count( $this->table_data );
        
        $this->table .= $this->table
            ? ',' . $table_placeholder
            : $table_placeholder;
        
        $this->table_data[ $table_placeholder ] = [ $table, 'table' ];
        
        return $this;
    }

    private function getFirstTablePlaceholder(): string
    {
        return array_keys( $this->table_data )[0];
    }
}
<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Columns{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $columns = '';
    private array  $columns_data = [];
    
    /**
     * Set columns to interact with
     *
     * @param array|string $columns array with columns names or comma-separated string values
     * @param string|null  $table   Use custom table for columns. !!! WITHOUT SANITIZATION !!!
     *
     * @return static
     */
    public function columns( array|string $columns, string $table = null ): static
    {
        // $table = $table ?? trim( $this->getFirstTablePlaceholder(), ':' );
        $table = $table ?? $this->getFirstTablePlaceholder();
        
        // Convert to array
        $columns = ! is_array( $columns )
            ? explode( ',', $columns )
            : $columns;
        
        // Append table name to columns: 'id' -> 'users.id'
        foreach( $columns as &$column ){
            
            if( $column === '*' ){
                
                $placeholder                        = ":column_all" . '_' . trim( $table, ':' );
                $this->columns_data[ $placeholder ] = [ '*', 'column_name' ];
                $column                             = "$table.$placeholder";
                
                continue;
            }
            
            $placeholder                        = ":column_$column" . '_' . trim( $table, ':' );
            $this->columns_data[ $placeholder ] = [ $column, 'column_name' ];
            $column                             = "$table.$placeholder";
        }
        unset( $column );
        
        $this->columns .= $this->columns
            ? ',' .implode( ',', $columns )
            : implode( ',', $columns );
        
        return $this;
    }
}
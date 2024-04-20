<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\Exceptions\DBException;

final class Join implements \Stringable
{
    private Select $select;
    
    private string $table;
    private array  $table_data;
    
    private string $join_table;
    private array  $join_table_data;
    
    private string $on;
    
    private array  $allowed_join_types = ['inner','outer','left','full outer',];
    private string $type;
    
    private string $columns;
    private string $where;
    
    public function __construct( string $join_table, $type, Select $select )
    {
        $this->select = $select;
        
        $this->joinTable( $join_table );
        $this->type( $type );
    }
    
    /**
     * Set table to query from\to
     *
     * @param string $table
     *
     * @return static
     */
    private function joinTable( string $join_table ): void
    {
        $this->join_table      = ':join_table';
        $this->join_table_data = [ ':join_table' => [ $join_table, 'table' ] ];
    }
    
    /**
     * Appends join block to request
     *
     * @param array $joins
     *      'user' => [
     *            'table'     => 'table name',
     *            'condition' => [ 'user_id' => 'id', ],
     *            'type'      => 'inner|outer|left|full outer|...'
     *            'columns'   => [ 'inn' ],
     *      ],
     *
     * @return static
     */
    // public function join( array $joins ): static
    // {
    //     foreach( $joins as $relation => &$join ){
    //
    //         $join_table    = $join['classname'] instanceof ObjectEntity ? $join['classname']::table() : $join['classname'];
    //         $on            = "$this->table." . key( $join['condition'] ) . " = $join_table." . current( $join['condition'] );
    //         $type          = $join['type'] ?? 'INNER';
    //         $join_columns  = $join['columns'] ?? $join['classname']::_getColumns();
    //         // $where        .= $join['condition']
    //         //     ? ' AND ' . implode(
    //         //         ' AND ',
    //         //         array_map(
    //         //             static function( $column ) use ( $join_table, $conditions ){
    //         //                     return "$join_table.$column = ?";
    //         //                 },
    //         //             $join['condition']
    //         //         )
    //         //     )
    //         //     : '';
    //         $this->columns .= ', ' . implode(
    //                 ', ',
    //                 array_map(
    //                     function( $column ) use ( $join_table, $relation ){
    //                         return "$join_table.$column AS " . $this->wrap( "$relation.$column" );
    //                     },
    //                     $join_columns
    //                 )
    //             );
    //         $join = "$type JOIN $join_table ON $on"; // INNER JOIN elements ON blocks.id = elements.parent
    //     } unset( $join );
    //
    //     $this->joins = implode( ' ', $joins );
    //
    //     return $this;
    // }
    
    public function on( $column_main ,$column_secondary )
    {
        // $this->
        
        return $this->select;
    }
    
    public function type( $type ): void
    {
        ! in_array( $type, $this->allowed_join_types, true ) &&
            throw new DBException("Join type '$type' is not allowed");
        
        $this->type = $type;
    }
    
    public function columns( array $columns ): self
    {
        return $this;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
    
    }
}
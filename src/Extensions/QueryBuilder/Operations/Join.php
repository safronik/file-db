<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\Exceptions\DBException;
use Safronik\DB\Extensions\QueryBuilder\Select;

trait Join
{
    private Select $select;
    
    private int    $join_count = 1;
    private string $joins = '';
    private string $join_columns = '';
    private array  $join_data = [];
    
    private array $allowed_join_types = ['inner','left','right',];
    private array $allowed_join_operators = [
        '=',
        '<=>',
        '!=',
        '>',
        '<',
        '>=',
        '<=',
    ];
    
    
    /**
     * Appends join block to request
     *
     * @param array       $condition [ [ 'join_table', 'column' ], '=', [ 'base_table', 'column' ] ]
     * @param string      $join_type 'inner'|'left'|'right'
     * @param array|false $columns   [ 'id' ]
     * @param string|null $column_prefix
     *
     * @return static
     * @throws \Exception
     */
    public function join( array $condition, string $join_type = 'inner', array|false $columns = [], string $column_prefix = null ): static
    {
        // Process type
        $join_type = $this->joinType( $join_type );
        
        // Process condition
        $condition      = $this->standardizeJoinCondition( 1, $condition );
        $join_condition = $this->processJoinCondition( $condition );
        
        // Process join table
        $join_table                                 = $condition[0]['table'];
        $join_table_placeholder                     = ":join_table" . count( $this->join_data );
        $this->join_data[ $join_table_placeholder ] = [ $join_table, 'table' ];
        
        // Append join
        $join        = "$join_type JOIN $join_table_placeholder ON $join_condition";
        $this->joins .= "\n $join";
        
        // Append columns to select
        // $this->columns( $columns, $condition[0]['table'] );
        $this->join_columns .= $columns === false
            ? ''
            : $this->appendJoinColumns( $columns, $column_prefix, $join_table_placeholder, $join_table );
        
        return $this;
    }
    
    private function joinType( $type ): string
    {
        ! in_array( strtolower( $type ), $this->allowed_join_types, true ) &&
            throw new DBException("Join type '$type' is not allowed");
        
        return strtoupper( $type );
    }
    
    /**
     * @param $columns
     * @param $join_table_placeholder
     * @param $join_table
     *
     * @return string
     *
     * @todo fix possible SQL-injection via columns
     */
    private function appendJoinColumns( $columns, $column_prefix, $join_table_placeholder, $join_table ): string
    {
        $column_prefix = $column_prefix ?? $join_table_placeholder;
        
        // Get all columns of join table if they aren't set
        if( ! $columns ){
            $schema = $this->db
                ->setResponseMode( 'array' )
                ->prepare(
                    'SHOW COLUMNS FROM :table;',
                    [ ':table' => [ $join_table, 'table' ] ]
                )
                ->query()
                ->fetchAll();
            $columns = array_column( $schema, 'Field' ) ?: array_column( $schema, 'field' );
        }
        
        // Append join columns to select statement, to parse the results later.
        // For example: join_table.column as "join_table.column"
        return ', ' . implode(
            ', ',
            array_map(
                static function( $column ) use ( $join_table_placeholder, $column_prefix ){
                    return "$join_table_placeholder.$column AS \"$column_prefix.$column\"";
                },
                $columns
            )
        );
    }
    
    private function processJoinCondition( $condition ): string
    {
        $where[0] = $this->processJoinOperand( $condition[0] );
        $where[1] = strtoupper( $condition[1] );
        $where[2] = $this->processJoinOperand( $condition[2] );
        
        return implode( ' ', $where );
    }
    
    private function processJoinOperand( $operand )
    {
        if( isset( $operand['expression'] ) ){
            $placeholder                      = ":join_expression" . count( $this->join_data );
            $this->join_data[ $placeholder ] = [ $operand['expression'], 'no_sanitization' ];
        }
        if( isset( $operand['table'] ) ){
            $table_placeholder                      = ':join_' . $operand['table'] . count( $this->join_data );
            $this->join_data[ $table_placeholder ] = [ $operand['table'], 'table', ];
        }
        if( isset( $operand['column'] ) ){
            $placeholder                      = ':join_' . $operand['column'] . count( $this->join_data );
            $this->join_data[ $placeholder ] = [ $operand['column'], 'column_name', ];
        }
        
        return ( isset( $table_placeholder ) ? "$table_placeholder." : '') . $placeholder;
    }
    
    private function standardizeJoinCondition( $column, $condition ): array
    {
        // [ 'column' => Expression::class ]
        // [ 'column' => [ 'operator', Expression::class ] ]
        if( is_string( $column ) ){
            
            $operands[]['column'] = $column;
            
            if( is_array( $condition ) ){
                
                $operator   = strtolower( $condition[0] );
                $operands[] = match( true ){
                    is_array( $condition[1] )                                          => [ 'set' => $condition[1] ],
                    is_subclass_of( $condition[1], BaseOperation::class ) => [ 'expression' => $condition[1] ],
                };
                
            }else{
                $operator = '=';
                $operands[] = match( true ){
                    is_subclass_of( $condition, BaseOperation::class ) => [ 'expression' => $condition ],
                };
            }
            
            $operator = is_array( $condition )
                ? strtolower( $condition[0] )
                : '=';
            
            $operands[]['column'] = is_array( $condition )
                ? $condition[1]
                : $condition;
            
        // [ 'column', Expression::class ]
        // [ 'column', 'operator', Expression::class, ]
        // [ 'table' ,'column' ], 'operator', [ 'table' ,'column' ], ]
        }else{
            
            $operator = array_reduce(
                $condition,
                fn( $carry, $value ) =>
                    in_array( $value, $this->allowed_join_operators, true )
                        ? $value
                        : $carry,
                '='
            );
            
            $operand_key = array_search( $operator, $condition, true );
            if( $operand_key !== false ){
                unset( $condition[ $operand_key ] );
            }
            
            $operands = [];
            foreach( $condition as $item ){
                
                $operands[] = match( true ){
                    is_scalar( $item ) && empty( $operands[0] )                => [ 'column' => $item ],
                    is_array( $item )  && empty( $operands[0] )                => [ 'table' => reset($item), 'column' => next($item) ],
                    is_array( $item )                                          => [ 'table' => reset($item), 'column' => next($item) ],
                    is_subclass_of( $item, BaseOperation::class ) => [ 'expression' => $item ],
                };
            }
        }
        
        return [ $operands[0], $operator, $operands[1] ];
    }
}
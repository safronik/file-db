<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Where{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $where = '';
    private array  $where_data = [];
    
    private array $allowed_operators = [
        '=',
        '<=>',
        '!=',
        '>',
        '<',
        '>=',
        '<=',
        'is',
        'in',
        'like',
    ];
    
    /**
     * Set where string from passed array
     *
     * @param array $conditions Examples:
     *      [
     *          'column_to_compare' => [
     *              'in', // Operator
     *              ['string_value', 10, 'another_string_value'], // Operand
     *          ]
     *      ]
     *      [
     *           'column_to_compare' => [
     *               'like', // Operator
     *               ['string_val%'], // Operand
     *           ]
     *       ]
     *       [
     *          [ 'table1' , 'column' ],
     *          '=',
     *          [ 'table2' , 'column' ],
     *       ]
     *      [
     *          [ 'table1' , 'column' ],
     *          '>=',
     *          'value',
     *      ]
     * @param ?string $table
     *
     * Supported operators are: '=', '!=', '>', '<',' >=', '<=', 'in', 'like', 'is'
     *
     * @return $this
     *@throws \Exception
     *
     */
    public function where( array $conditions ): static
    {
        if( ! $conditions ){
            return $this;
        }
        
        // Prepend key word
        $this->where .= $this->where
            ? "\n"
            : "WHERE\n"; // prepend key phrase if it's the first call
        
        $where = [];
        foreach( $conditions as $column => $condition ){
            $condition = $this->standardizeCondition( $column, $condition );
            $where[]   = $this->processCondition( $condition );
        }
        
        $this->where .= implode( ' AND ', $where );
        
        return $this;
    }
    
    public function processCondition( $condition ): string
    {
        $where[0] = $this->processOperand( $condition[0] );
        $where[1] = strtoupper( $condition[1] );
        $where[2] = $this->processOperand( $condition[2], $condition[0] );
         
        return implode( ' ', $where );
    }
    
    private function processOperand( $operand, $column_operand = null )
    {
        $column = $column_operand ? $column_operand['column'] : null;
        
        if( isset( $operand['expression'] ) ){
            $placeholder                      = ":where_expression" . count( $this->where_data );
            $this->where_data[ $placeholder ] = [ $operand['expression'], 'no_sanitization' ];
        }
        if( isset( $operand['table'] ) ){
            $table_placeholder                      = ':where_' . $operand['table'] . count( $this->where_data );
            $this->where_data[ $table_placeholder ] = [ $operand['table'], 'table', ];
        }
        if( isset( $operand['column'] ) ){
            $placeholder                      = ':where_' . $operand['column'] . count( $this->where_data );
            $this->where_data[ $placeholder ] = [ $operand['column'], 'column_name', ];
        }
        if( isset( $operand['set'] ) ){
            // Adding operands via unnamed placeholders
            foreach( $operand['set'] as &$item ){
                $tmp                      = ":where_value_in_$column" . count( $this->where_data );
                $this->where_data[ $tmp ] = [ $item, ];
                $item                     = $tmp;
            }
            unset( $item );
            
             $placeholder  = '(' . implode( ',', $operand['set'] )  . ')';
        }
        if( isset( $operand['value'] ) ){
            $placeholder = ":where_value_$column" . count( $this->where_data );
            $this->where_data[ $placeholder ] = [ $operand['value'], ];
        }
        
        return ( isset( $table_placeholder ) ? "$table_placeholder." : '') . $placeholder;
    }
    
    private function standardizeCondition( $column, $condition ): array
    {
        // [ 'column' => 'value' ]
        // [ 'column' => Expression::class ]
        
        // [ 'column' => [ 'operator', 'value' ] ]
        // [ 'column' => [ 'operator', Expression::class ] ]
        // [ 'column' => [ 'in', [ 'set_value_1', 'set_value_2',] ] ]
        if( is_string( $column ) ){
            
            $operands[]['column'] = $column;
            
            if( is_array( $condition ) ){
                
                $operator   = strtolower( $condition[0] );
                $operands[] = match( true ){
                    is_scalar( $condition[1] )                                         => [ 'value' => $condition[1] ],
                    is_array( $condition[1] )                                          => [ 'set' => $condition[1] ],
                    is_subclass_of( $condition[1], BaseOperation::class ) => [ 'expression' => $condition[1] ],
                };
                
            }else{
                $operator = '=';
                $operands[] = match( true ){
                    is_scalar( $condition )                                         => [ 'value' => $condition ],
                    is_subclass_of( $condition, BaseOperation::class ) => [ 'expression' => $condition ],
                };
            }
            
            $operator = is_array( $condition )
                ? strtolower( $condition[0] )
                : '=';
            
            $operands[]['column'] = is_array( $condition )
                ? $condition[1]
                : $condition;
            
        // [ 'column', 'value' ]
        // [ 'column', Expression::class ]
        
        // [ 'column', 'operator', 'value', ]
        // [ 'column', 'operator', Expression::class, ]
        // [ 'column', 'in', [ 'set_value_2', 'set_value_2',], ]
        
        // [ 'table' ,'column' ], 'operator', [ 'table' ,'column' ], ]
        // [ 'table' ,'column' ], 'in', [ 'set_value_2', 'set_value_2', ], ]
        }else{
            
            $operator = array_reduce(
                $condition,
                fn( $carry, $value ) =>
                    in_array( $value, $this->allowed_operators, true )
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
                    is_scalar( $item )                                         => [ 'value' => $item ],
                    is_array( $item ) && $operator === 'in'                    => [ 'set' => $item ],
                    is_array( $item )                                          => [ 'table' => reset($item), 'column' => next($item) ],
                    is_subclass_of( $item, BaseOperation::class ) => [ 'expression' => $item ],
                };
            }
        }
        
        return [ $operands[0], $operator, $operands[1] ];
    }
    
    public function and( $condition )
    {
        $this->where .= ' AND ';
        
        return $this->where( $condition );
    }
    
    public function or( $condition )
    {
        $this->where .= ' OR ';
        
        return $this->where( $condition );
    }
}
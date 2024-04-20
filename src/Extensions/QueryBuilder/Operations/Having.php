<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\Exceptions\DBException;

trait Having{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $having = '';
    private array  $having_data = [];
    
    private array $allowed_having_operators = [
        '=',
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
     * Set having string from passed array
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
     * @param ?string $table
     *
     * Supported operators are: '=', '!=', '>', '<',' >=', '<=', 'in', 'like', 'is'
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function having( array $conditions, ?string $table = null ): static
    {
        if( ! $conditions ){
            return $this;
        }
        
        $table = $table ?? $this->getFirstTablePlaceholder();
        
        $this->having .= $this->having
            ? "\n"
            : "HAVING\n"; // prepend key phrase if it's the first call
        
        $having = [];
        foreach( $conditions as $column => $condition ){
            
            $operator = ! is_array( $condition )
                ? '='
                : strtolower( $condition[0] );
            
            $operand = ! is_array( $condition )
                ? $condition
                : $condition[1];
            
            in_array( $operator, $this->allowed_having_operators, true )
                || throw new DBException('Unsupported operator');
            
            switch( $operator ){
                
                case 'in':
                    
                    $column_placeholder = ":having_$column" . count( $this->having_data );
                    
                    // Adding operands via unnamed placeholders
                    foreach( $operand as $key => &$item ){
                        $column_value_in_placeholder                      = ":having_value_in_$column" . count( $this->having_data );
                        $this->having_data[ $column_value_in_placeholder ] = [ $item, ];
                        $item                                             = $column_value_in_placeholder;
                    } unset( $item );
                    
                    $operands_string  = '(' . implode( ',', $operand )  . ')';
                    
                    $having[] = "$table.$column_placeholder IN $operands_string"; // Add placeholders
                    $this->having_data[ $column_placeholder ] = [ $column, 'column_name', ];
                    break;
                    
                // Simple operands is,like,=,!=,>,<,>=,<=
                default:
                    
                    $column_placeholder       = ":having_$column" . count( $this->having_data );
                    $column_value_placeholder = ":having_value_$column" . count( $this->having_data );
                    
                    $this->having_data[ $column_placeholder ]       = [ $column, 'column_name', ];
                    $this->having_data[ $column_value_placeholder ] = (array) $operand;
                    
                    $having[] = "$table.$column_placeholder $operator $column_value_placeholder"; // Add placeholders
            }
        }
        
        $this->having .= implode( ' AND ', $having );
        
        return $this;
    }
    
    public function andHaving( $condition )
    {
        $this->having .= ' AND ';
        
        return $this->having( $condition );
    }
    
    public function orHaving( $condition )
    {
        $this->having .= ' OR ';
        
        return $this->having( $condition );
    }
}
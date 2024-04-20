<?php

namespace Safronik\DB\Extensions;

trait PreparedQueryExtension
{
    private array $named_placeholders   = [];
    private array $unnamed_placeholders = [];
    
    private string $query_to_prepare;
    
    /**
     * Safely replace placeholders in any part of query
     *
     * Doesn't create a prepared statement.
     * Look for $this->prepare if you want to make multiple similar queries.
     *
     * @param string $query
     * @param array  $values
     *
     * @return static
     */
	public function prepare( string $query, array $values, bool $return_query = false ): static|string
    {
        $this->query_to_prepare     = $query;
        $values                     = $this->parseValues( $values );
        $this->named_placeholders   = $this->getNamedPlaceholders( $values );
        $this->unnamed_placeholders = $this->getUnnamedPlaceholders( $values );
        
        $this->named_placeholders   && $this->preparePlaceholders( $this->named_placeholders,   'named' );
        $this->unnamed_placeholders && $this->preparePlaceholders( $this->unnamed_placeholders, 'unnamed' );
        
        $this->cleanUpPlaceholders();
        
        if( $return_query ){
            return $this->query_to_prepare;
        }
        
        $this->query = $this->query_to_prepare;
        
        return $this;
	}
    
    private function parseValues( $values )
    {
        array_walk(
            $values,
            static function( &$value, $name ){
                $value = ! is_array( $value )  ? [ $value,    'string' ] : $value; // Append type if scalar     passed
                $value = count( $value ) === 1 ? [ $value[0], 'string' ] : $value; // Append type if only value passed
            },
        );
        
        return $values;
    }
    
    private function getNamedPlaceholders( array $placeholders ): array
    {
        return array_filter(
            $placeholders,
            static function( $value, $name ){
                return count( $value ) === 3 || preg_match( '@^:[a-zA-Z0-9_-]+$@', $name );
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
    
    private function getUnnamedPlaceholders( array $placeholders ): array
    {
        return array_filter(
            $placeholders,
            static function( $value, $name ){
                return is_int( $name );
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
    
    /**
     * Bind values to prepared query
     * Supports named(:named) and unnamed(?) placeholders
     *
     * @param array  $values
     * @param string $placeholders_type
     *
     * @return static
     */
    private function preparePlaceholders( array $values = [], string $placeholders_type = 'unnamed' ): static
    {
        if( $placeholders_type === 'named'){
            foreach( $values as $name => $value ){
                $this->preparePlaceholder(
                    $value[0],
                    $value[1],
                    $name
                );
            }
        }
        
        if( $placeholders_type === 'unnamed'){
            for($i = 0, $value_size = count($values); $i < $value_size; $i++){
                $this->preparePlaceholder(
                    $values[ $i ][0],
                    $values[ $i ][1]
                );
            }
        }
        
        return $this;
    }
    
    /**
     * Bind a single placeholders with its value
     * Supports named(:named) and unnamed(?) placeholders
     *
     * @param string|int  $value
     * @param string      $type
     * @param string|null $name
     *
     * @return void
     */
    private function preparePlaceholder( string|int $value, string $type = 'string', string $name = null ): void
    {
        $sanitized_value        = $this->sanitize( $value, $type );
        $this->query_to_prepare = $name
            ? preg_replace( '@' . $name . '(?=([^a-zA-Z0-9_-]{1}|$))@',           $sanitized_value, $this->query_to_prepare)
            : preg_replace( '/\s?\?\s?/', ' ' . $sanitized_value . ' ', $this->query_to_prepare, 1 );
    }
    
    private function cleanUpPlaceholders(): void
    {
        $this->named_placeholders   = [];
        $this->unnamed_placeholders = [];
    }
    
    /**
     * @param bool|int|string|null $value
     * @param string               $type
     *
     * @return array|int|string|null
     */
    private function sanitize( bool|int|string|null $value, string $type = 'string' ): array|int|string|null
    {
        switch($type){
            case 'table':
                $sanitized_value = preg_replace( '/[^\w\d._-]/', '', $value);
                break;
            case 'column_name':
                $sanitized_value = preg_replace( '/[^*\w\d_-]/', '', $value);
                break;
            case 'limit':
                $sanitized_value = preg_replace( '/\D/', '', $value);
                break;
            case 'order_by':
                $sanitized_value = preg_replace( '/[^\w\d._-]/', '', $value);
                break;
            case 'serve_word':
                $sanitized_value = preg_replace('/[^\w\s]/', '', $value);
                break;
            case 'function':
                $sanitized_value = preg_replace('/[^\w\s()]/', '', $value);
                break;
            case 'no_sanitization':
                $sanitized_value = $value;
                break;
            case 'string':
                $sanitized_value = $this->driver->sanitize( (string) $value );
                break;
            case 'int':
                $sanitized_value =  (int) $value;
                break;
            case 'bool':
                $sanitized_value =  $value ? 'TRUE' : 'FALSE';
                break;
            case 'null':
                $sanitized_value =  'NULL';
                break;
                
            // Consider empty type as a 'string' type
	        default:
				$sanitized_value = $this->sanitize( $value );
				break;
        }
        
        return $sanitized_value;
    }
}
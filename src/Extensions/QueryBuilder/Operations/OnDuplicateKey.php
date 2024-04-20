<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\Exceptions\DBException;

trait OnDuplicateKey{

    private string $on_duplicate_key = '';
    private array  $on_duplicate_key_data = [];
    private array  $on_duplicate_key_value_data = [];

    /**
     * Process and appends ON DUPLICATE KEY SQL-key-phrase
     *
     * Supports:
     * - increment
     * - update
     *
     * @param string $type
     * @param array  $columns
     *
     * @return self
     * @throws \Exception
     */
    public function onDuplicateKey( string $type, array $columns ): self
    {
        $this->on_duplicate_key .= $this->on_duplicate_key
            ? ",\n"
            : "ON DUPLICATE KEY UPDATE\n"; // prepend key phrase if it's the first call
        
        switch( $type ){
            case 'increment': $this->onDuplicateKeyAppendIncrement( $columns ); break;
            case 'update':    $this->onDuplicateKeyAppendUpdate( $columns );    break;
            default: throw new DBException("OnDuplicateKey modifier '$type' not found. Supported are: 'increment', 'update'.");
        }
        
        return $this;
    }
    
    /**
     * Increment specific columns when ON DUPLICATE KEY
     *
     * @param array $columns
     *
     * @return void
     */
    private function onDuplicateKeyAppendIncrement( array $columns ): void
    {
        $on_duplicate_key = [];
        foreach( $columns as $column ){
            $on_duplicate_key[] = ":on_duplicate_key_$column = :on_duplicate_key_$column + 1"; // Add placeholders
            $this->on_duplicate_key_data[ ":on_duplicate_key_$column" ] = [ $column, 'column_name' ]; // Add placeholders data
        }
        
        $this->on_duplicate_key .= implode( ",\n", $on_duplicate_key );
    }
    
    /**
     * Updates specific columns when ON DUPLICATE KEY
     *
     * @param array $columns
     *
     * @return void
     */
    private function onDuplicateKeyAppendUpdate( array $columns ): void
    {
        // Increment specific columns
        $on_duplicate_key = [];
        foreach( $columns as $column => $value ){
            $on_duplicate_key[] = ":on_duplicate_key_$column = :on_duplicate_key_value_$column";      // Add placeholders
            $this->on_duplicate_key_data[ ":on_duplicate_key_$column" ] = [ $column, 'column_name' ]; // Add placeholders for column
            $this->on_duplicate_key_value_data[ ":on_duplicate_key_value_$column" ] = [ $value ];     // Add placeholders for data
        }
        
        $this->on_duplicate_key .= implode( ",\n", $on_duplicate_key );
    }
}
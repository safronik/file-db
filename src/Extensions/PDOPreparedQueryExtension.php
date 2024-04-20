<?php

namespace Safronik\DB\Extensions;

trait PDOPreparedQueryExtension
{
    /**
     * @var string Shows if the placeholders named (:placeholder) or unnamed (?)
     */
    private string $placeholders_type;
    
    /**
     * @var bool Show if the prepared query contains placeholders
     */
    private $query_is_prepared = false;
    
	public $execute_result = '';
    
    /**
     * Bind values to prepared query
     *
     * @param $values
     *
     * @return $this
     */
    public function bindValues( $values = array() )
    {
        if( $this->placeholders_type === 'named'){
            foreach( $values as $value_data ){
                $type = $value_data[2] ?? 'string';
                $this->driver->prepared_statement->bindValue( $value_data[0], $value_data[1], $this->driver->convertPlaceholdersType( $type ) );
            }
        }
        
        if( $this->placeholders_type === 'unnamed'){
            for($i = 1, $value_size = count($values); $i <= $value_size; $i++){
                $type = $values[ $i - 1 ][1] ?? 'string';
                $this->driver->prepared_statement->bindValue( $i, $values[ $i - 1][0], $this->driver->convertPlaceholdersType( $type ) );
            }
        }
        
        return $this;
    }
	
    /**
     * Creates prepared statement for multiple queries
     *
     * Allows to make quick queries with different parameters
     * Prepare DB by making a query plan
     * Safely replace placeholders
     *
     * <br>
     * <b>Supports placeholder only for parameters ( no tables or columns names are supported )</b>
     *
	 *
	 * @param string $query
	 * @param array  $values
	 *
	 * @return self
     */
	public function createPreparedQuery( $query = null, $values = [] ): static
    {
        $this->query             = $query ?? $this->query;
        $this->query_is_prepared = $this->driver->prep( $this->query );
        $this->placeholders_type = str_contains( $this->query, ' ? ' ) ? 'unnamed' : 'named';
        if( $values ){
            $this->bindValues( $values );
        }
        
        return $this;
	}
    
    /**
     * Executes prepared statement
     *
     * @param $params
     *
     * @return self
     */
    public function executePreparedQuery( $params = array() ): static
    {
        $this->execute_result = $this->driver->execute( $params );
        
        return $this;
    }
}
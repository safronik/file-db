<?php

namespace Safronik\DB\Drivers;

use PDOStatement;
use Safronik\DB\Exceptions\DBException;

class PDO extends \PDO implements DBDriverInterface
{
    /**
	 * @var string
	 */
	public $query = '';
    
    /**
     * @var null|false|PDOStatement
     */
    public $prepared_statement;
    
	/**
	 * @var PDOStatement
	 */
	public $result;
	
	/**
	 * @var int
	 */
	public $rows_affected;
    
    public function q( $query )
    {
        return parent::query( $query );
    }
    
    /**
     * @param string $query
     * @param array  $options
     *
     * @return bool
     */
    public function prep( $query, $options = [] )
    {
        $this->prepared_statement = $this->prepare( $query, $options );
        
        $this->prepared_statement === false &&
            throw new DBException('failed');
        
        $this->query = $this->prepared_statement->queryString;
        
        return true;
    }
    
    public function execute( $params = array() )
    {
        $result = ! empty( $params )
            ? $this->prepared_statement->execute( $params )
            : $this->prepared_statement->execute();
    
        $this->result = $this->prepared_statement;
        
        return $result;
    }
    
    /**
     * @param string $statement
     *
     * @return bool|int
     */
	public function exec( $statement ): int|false
    {
		$this->query         = $statement;
		$this->rows_affected = parent::exec( $statement );
  
		return $this->rows_affected;
	}
    
    /**
	 * Fetch first column from query.
	 * May receive raw or prepared query.
	 *
	 * @param string $response_type
	 *
	 * @return array|object|void|null
	 */
	public function fetch( $response_type = 'array' )
    {
		return $this->result->fetch(
            $this->convertResponseType( $response_type )
        );
	}
    
    /**
     * Fetch all result from query.
     * May receive raw or prepared query.
     *
     * @param string               $response_type
     * @param null|callable|string $fetch_argument
     *
     * @return array|false
     */
	public function fetchAll( $response_type = 'array', $fetch_argument = null )
    {
        if( $fetch_argument === null ){
            return $this->result->fetchAll(
                $this->convertResponseType( $response_type )
            );
        }
        
        return $this->result->fetchAll(
            $this->convertResponseType( $response_type ),
            $fetch_argument
        );
	}
    
    public function convertPlaceholdersType( $response_type )
    {
        switch($response_type){
			case 'int':
				return \PDO::PARAM_INT;
			case 'string':
				return \PDO::PARAM_STR;
			case 'bool':
				return \PDO::PARAM_BOOL;
            case 'null':
                return \PDO::PARAM_NULL;
		}
        
        return \PDO::PARAM_STR;
    }

    
    private function convertResponseType( $response_type )
    {
        switch($response_type){
			case 'array':
				return \PDO::FETCH_ASSOC;
			case 'object':
				return \PDO::FETCH_OBJ;
			case 'numeric':
				return \PDO::FETCH_NUM;
            case 'func':
				return \PDO::FETCH_FUNC;
		}
        
        return \PDO::FETCH_ASSOC;
    }
	
	public function getAffectedRowCount(){
		return $this->result ? $this->result->rowCount() : 0;
	}
    
    public function getSelectedRowCount()
    {
        return $this->result ? $this->result->rowCount() : 0;
    }
    
    public function sanitize( $arg )
    {
        return $this->quote( $arg );
    }
}
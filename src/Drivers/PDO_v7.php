<?php

namespace Safronik\DB\Drivers;

use PDO;
use PDOStatement;

class PDO_v7 extends \PDO{
	
	/**
	 * Safely replace placeholders
	 *
	 * @param string $query
	 * @param array  $options
	 *
	 * @return bool|PDOStatement
	 */
	public function prepare( $query, $options = NULL )
    {
		return parent::prepare( $query, $options );
	}
    
    /**
	 * Executes a query to DB
	 *
	 * @param string $statement
	 * @param int    $mode
	 *
	 * @return false|PDOStatement
	 */
	public function query( $statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = [] )
    {
		$this->query         = $statement;
		$this->result        = parent::query( $statement );
		$this->rows_affected = $this->driver->getRowCount();
  
		return $this->result;
	}
}
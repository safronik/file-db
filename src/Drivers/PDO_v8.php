<?php

namespace Safronik\DB\Drivers;

use PDOStatement;
use Safronik\DB\Exceptions\DBException;

class PDO_v8 extends PDO
{
    /**
     * Safely replace placeholders
     *
     * @param string $query
     * @param array  $options
     *
     * @return bool|PDOStatement
     *@throws DBException
     *
     */
	public function prep( $query, $options = [] ) {
		return parent::prep( $query, $options );
	}
    
    /**
     * @param string   $query
     * @param int|null $fetchMode
     * @param mixed    ...$fetchModeArgs
     *
     * @return false|PDOStatement
     */
    public function q( $query )
    {
        $this->query         = $query;
        $this->result        = parent::query( $query );
        
        return $this->result;
    }
}
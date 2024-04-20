<?php

namespace Safronik\DB\Drivers;

use WPDB;

use const Safronik\Services\DB\Drivers\ARRAY_A;
use const Safronik\Services\DB\Drivers\OBJECT_K;

class Wordpress implements DBDriverInterface{
	
	/**
	 * @var \WPDB
	 */
	private $wpdb;
	private $query;
	private $result;
	
	public function __construct( \WPDB $connection )
	{
		$this->wpdb   = $connection;
	}
	
	public function q( $query )
	{
		// Save result of SELECT like queries to fetch it later via fetchALL
		$query_type = explode( ' ', $query );
		$query_type = strtoupper( array_shift( $query_type ) );
		if( in_array( $query_type, ['SELECT', 'SHOW'])){
			$this->query = $query;
			
			return null;
		}
		
		return $this->wpdb->query( $query );
	}
	
	public function prep( $query, $options )
	{
	
	}
	
	public function execute( $params ){
		// TODO: Implement execute() method.
	}
	
	public function exec( $statement ){
		// TODO: Implement exec() method.
	}
	
	public function fetch( $response_type ){
		// TODO: Implement fetch() method.
	}
	
	public function fetchAll( $response_type = 'array', $fetch_argument = null )
	{
		return $this->result = $this->wpdb->get_results(
			$this->query,
			$this->convertResponseType( $response_type )
		);
	}
	
	public function convertPlaceholdersType( $response_type ){
		// TODO: Implement convertPlaceholdersType() method.
	}
	
	public function getAffectedRowCount()
	{
		return $this->wpdb->rows_affected;
	}
	
	public function getSelectedRowCount()
	{
		return $this->wpdb->num_rows;
	}
	
	public function sanitize( $arg )
	{
		return $this->wpdb->prepare( '%s', $arg);
	}
	
    private function convertResponseType( $response_type )
    {
        switch($response_type){
			case 'array':
				return ARRAY_A;
			case 'obj':
				return OBJECT_K;
	        default:
				return ARRAY_A;
		}
    }
}
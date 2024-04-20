<?php

namespace Safronik\DB\Drivers;

interface DBDriverInterface
{
	public function q( $query );
	public function prep( $query, $options );
	public function execute( $params );
	public function exec( $statement );
	public function fetch( $response_type );
	public function fetchAll( $response_type, $fetch_argument );
	public function convertPlaceholdersType( $response_type );
	public function getAffectedRowCount();
	public function getSelectedRowCount();
	public function sanitize( $arg );
}
<?php

namespace Safronik\DB;

use Safronik\CodePatterns\Generative\Singleton;
use Safronik\DB\Drivers\DBDriverInterface;
use Safronik\DB\Extensions\PreparedQueryExtension;
use Safronik\DB\Extensions\QueryBuilderExtension;
use Safronik\DB\Extensions\TableExtension;

class DB{
 
    // Code Templates
	use Singleton;
    
    // Extensions
    use TableExtension;
    use PreparedQueryExtension;
    use QueryBuilderExtension;
    
    private DBDriverInterface $driver;
    
    /** @var string Query string */
	public $query = null;
    
    /** @var string Valid values are 'array'|'obj'|'num' */
    private string $response_mode = 'array';
    
	/** @var mixed DB result. Could be anything, depends on a driver implementation */
	public $result = '';
 
	/** @var int Number of affected rows */
	private int $rows_affected;
    
    /** @var int Number of selected rows  */
	private int $rows_selected;
 
	/** @var string Common DB prefix for all tables */
    private string $db_prefix = '';
	
	/** @var string Application DB prefix used only by current application */
    private $app_prefix = '';
	
	/** @var string $this->db_prefix and $this->app_prefix combined */
	private string $full_prefix;
    
    /**
     * @param DBConfig $config
     */
    protected function __construct( DBConfig $config )
    {
        $driver_namespace = __NAMESPACE__ . '\Drivers\\';
		switch( $config->driver ){
			case 'PDO':
             
	            $driver_name  = $driver_namespace . 'PDO_v' . substr( phpversion('pdo'), 0, 1);
                
                // Using existing connection
                if( isset( $config->connection ) ){
                    $this->driver = new $driver_name(
                        $config->connection
                    );
                    
                // Creating a new connection
                }else{
                    $this->driver = new $driver_name(
                        $config->dsn,
                        $config->username,
                        $config->password,
                        $config->options
                    );
                }
				break;
    
			case 'Wordpress':
				$driver_name  = $driver_namespace . $config->driver;
	            $this->driver = new $driver_name(
					$config->connection
	            );
				break;
		}
  
		$this->db_prefix   = $config->db_prefix;
		$this->app_prefix  = $config->app_prefix;
        $this->full_prefix = $this->db_prefix . $this->app_prefix;
	}
    
    /**
     * Executes a query to DB and returns DB object for further processing
     *
     * @param string|null $query
     *
     * @return static
     */
    public function query( string $query = null ): static
    {
        $this->query         = $query ?? $this->query;
        
        $this->result        = $this->driver->q( $this->query );
        $this->rows_affected = $this->driver->getAffectedRowCount();
        $this->rows_selected = $this->driver->getSelectedRowCount();
        
        return $this;
    }

    /**
     * Fetch first column from query result
     *
     * @param string|null $response_mode
     *
     * @return array|object|void|null
     */
    public function fetch( string $response_mode = null )
    {
        return $this->driver->fetch( $response_mode ?: $this->response_mode );
    }
    
    /**
     * Fetch all result from query.
     * May receive raw or prepared query.
     *
     * @param string|null          $query
     * @param string|null          $response_mode
     * @param callable|string|null $fetch_argument
     *
     * @return array|object|null
     */
    public function fetchAll( string $query = null, string $response_mode = null, callable|string $fetch_argument = null ): object|array|null
    {
        if( $query ){
            $this->query( $query );
        }
		
        return $this->driver->fetchAll( $response_mode ?: $this->response_mode, $fetch_argument );
    }
    
    /**
     * @param string $response_mode
     *
     * @return static
     */
    public function setResponseMode( string $response_mode ): static
    {
        $this->response_mode = $response_mode;
        
        return $this;
    }
    
    /**
     * @return mixed|string
     */
    public function getAppPrefix(): mixed
    {
        return $this->app_prefix;
    }
    
    /**
     * @param mixed|string $app_prefix
     */
    public function setAppPrefix( mixed $app_prefix ): void
    {
        $this->app_prefix  = $app_prefix;
        $this->full_prefix = $this->db_prefix . $this->app_prefix;
    }
    
    public function getRowsAffected(): int
    {
        return $this->rows_affected;
    }
    
    public function getRowsSelected(): int
    {
        return $this->rows_selected;
    }
    
    public function getFullPrefix(): string
    {
        return $this->full_prefix;
    }
    
    public function getDbPrefix(): string
    {
        return $this->db_prefix;
    }
}
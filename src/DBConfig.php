<?php

namespace Safronik\DB;

use Safronik\CodePatterns\Structural\Hydrator;
use Safronik\DB\Exceptions\DBException;

class DBConfig{
    
    use Hydrator;
    
    /** DB class with connection, such as WPDB \*/
    public $connection;
    public $driver = 'PDO';
    public $hostname = 'localhost';
    public $port = 3306;
    public $charset = 'utf8';
    public $database = '';
    public $username = '';
    public $password = '';
    public $dsn = '';
    public $options = [];
    public $db_prefix = '';
    public $app_prefix = '';
    
    /**
     * @param array $params
     *
     * @throws DBException
     */
    public function __construct( array $params = [] )
    {
        $this->hydrate( $params );
        $this->standardizeDriverName( $this->driver );
        $this->setConfigForDriver( $this->driver );
    }
    
    /**
     * Check driver name and standardize it
     *
     * @param $driver
     *
     * @return void
     */
    public function standardizeDriverName( $driver ): void
    {
        $driver       = strtolower( $driver );
        $this->driver = match ( $driver ) {
            'wordpress' => 'Wordpress',
            'pdo'       => 'PDO',
            'mysqli'    => 'mysqli',
            default     => 'unknown',
        };
    }
    
    /**
     * Set config correspond to driver
     *
     * @param $driver
     *
     * @return void
     * @throws DBException
     */
    public function setConfigForDriver( $driver ): void
    {
        match ( $driver ) {
            'Wordpress' => $this->setWordpressConfig(),
            'PDO'       => $this->setPDOConfig(),
            'mysqli'    => $this->setMySQLiConfig(),
            default     => throw new DBException( 'Passed driver is not supported: ' . $driver )
        };
    }
    
    /**
     * Set WordPress config
     *
     * @return void
     */
    private function setWordpressConfig(): void
    {
        global $wpdb;
        
        $this->connection = $wpdb;
        $this->db_prefix  = $wpdb->prefix;
    }
    
    /**
     * Set PDO config
     *
     * @return void
     */
    private function setPDOConfig(): void
    {
        $this->dsn = "mysql:host=$this->hostname;dbname=$this->database;charset=$this->charset;port=$this->port";
        
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Handle errors as an exceptions
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC     // Set default fetch mode as associative array
        ];
    }
    
    /**
     * Set MySQLi config
     *
     * @return void
     */
    private function setMySQLiConfig(): void
    {
        $this->connection = new \mysqli(
            $this->hostname, $this->username, $this->password, $this->database, $this->port
        );
        $this->connection->set_charset( $this->charset );
    }
}
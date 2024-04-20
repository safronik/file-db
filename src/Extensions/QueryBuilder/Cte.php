<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\DB;
use Safronik\DB\Exceptions\DBException;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Operations\Table;

class Cte extends BaseOperation
{
    use Table;
    
    private string $anchor;
    private string $recursive;
    
    public function __construct( string $name, DB $db )
    {
        $this->db = $db;
        
        $this->table( $name );
    }
    
    public function anchor( Select $anchor ): self
    {
        $this->anchor = (string) $anchor;
        
        return $this;
    }
    
    public function recursive( Select $recursive ): self
    {
        $this->recursive = (string) $recursive;
        
        return $this;
    }

    
    /**
     * Check that everything is ready for compilation
     *
     * @return void
     * @throws \Exception
     */
    protected function check(): void
    {
        $this->table || throw new DBException('No table set for request');
    }
    
    /**
     * Various preparation specific for each operation
     *
     * @return void
     */
    protected function filter(): void
    {
        // Append table.* to columns if it's empty
        // $this->columns || $this->columns('*' );
    }
    
    /**
     * Compiles the operation query.<br>
     * Puts into db->query
     *
     * @return void
     */
    protected function compile(): string
    {
        // Compile
        $query_string = "WITH RECURSIVE
                            $this->table
                            AS (
                                $this->anchor
                                UNION ALL
                                $this->recursive
                            )";
        
        // Delete redundant spaces symbols
        $query_string = preg_replace( "@\s{2,}@", ' ', $query_string);
        
        // Replace placeholders with data
        return $this->db->prepare(
            $query_string,
            array_merge(
                $this->table_data,
            ),
            true
        );
    }
    
    /**
     * Fires checked, filtered, complied query and fetch all data from the result
     *
     * @return object|array|null
     * @throws \Exception
     */
    public function run(): object|array|null
    {
        parent::run();
        
        return $this->db->fetchAll();
    }
}
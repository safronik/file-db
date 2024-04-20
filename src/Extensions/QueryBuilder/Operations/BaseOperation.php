<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\DB;

abstract class BaseOperation implements \Stringable{
    
    protected DB     $db;
    protected string $query;
    /**
     * Check that everything is ready for compilation
     *
     * @return void
     * @throws \Exception
     */
    abstract protected function check(): void;
    
    /**
     * Various preparation specific for each operation
     *
     * @return void
     */
    abstract protected function filter(): void;
    
    /**
     * Compiles the operation query.<br>
     * Puts into db->query
     *
     * @return void
     */
    abstract protected function compile(): string;
    
    /**
     * Fires checked, filtered, complied query
     *
     * @return object|array|null
     * @throws \Exception
     */
    protected function run()
    {
        $this->db->query(
            $this->prepare()
        );
    }

    protected function prepare(): string
    {
        $this->check();
        $this->filter();
        
        return $this->compile();
    }
    
    public function __toString(): string
    {
        return $this->prepare();
    }
}
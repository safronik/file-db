<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\DB;
use Safronik\DB\Exceptions\DBException;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Operations\Limit;
use Safronik\DB\Extensions\QueryBuilder\Operations\OrderBy;
use Safronik\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\DB\Extensions\QueryBuilder\Operations\Where;

final class Delete extends BaseOperation
{
    use Table;
    use Where;
    use OrderBy;
    use Limit;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Fires checked, filtered, complied query and fetch all data from the result
     *
     * @return string|null
     * @throws \Exception
     */
    public function run(): ?string
    {
        parent::run();
        
        return $this->db->getRowsAffected();
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
    protected function filter(): void {}
    
    /**
     * Compiles the operation query.<br>
     * Puts into db->query
     *
     * @return void
     */
    protected function compile(): string
    {
        return $this->db->prepare(
            "DELETE FROM $this->table\n$this->where\n$this->order_by\n$this->limit",
            array_merge(
                $this->table_data,
                $this->where_data,
                $this->order_by_data
            ),
            true
        );
    }
}
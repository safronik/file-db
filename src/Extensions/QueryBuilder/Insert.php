<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\DB;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Operations\Columns;
use Safronik\DB\Extensions\QueryBuilder\Operations\Ignore;
use Safronik\DB\Extensions\QueryBuilder\Operations\OnDuplicateKey;
use Safronik\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\DB\Extensions\QueryBuilder\Operations\Values;
use Safronik\DB\Exceptions\DBException;

final class Insert extends BaseOperation
{
    use Table;
    use Columns;
    use Ignore;
    use Values;
    use OnDuplicateKey;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Fires complied query and fetch all data from the result
     *
     * @return int
     * @throws \Exception
     */
    public function run(): int
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
        $this->table   || throw new DBException('No table set for request');
        $this->columns || throw new DBException('No columns set for request');
        $this->values  || throw new DBException('No values set for request');
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
            "INSERT $this->ignore INTO $this->table\n($this->columns)\nVALUES\n$this->values\n$this->on_duplicate_key",
            array_merge(
                $this->table_data,
                $this->columns_data,
                $this->values_data,
                $this->on_duplicate_key_data,
                $this->on_duplicate_key_value_data
            ),
            true
        );
    }
}
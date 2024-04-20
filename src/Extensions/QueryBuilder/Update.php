<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\DB;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Operations\Set;
use Safronik\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\DB\Extensions\QueryBuilder\Operations\Where;
use Safronik\DB\Exceptions\DBException;

final class Update extends BaseOperation
{
    use Table;
    use Where;
    use Set;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Fires checked, filtered, complied query and fetch all data from the result
     *
     * @return object|array|null
     * @throws \Exception
     */
    public function run(): ?string
    {
        parent::run();
        
        return $this->db->getRowsAffected();
    }
    
    protected function check(): void
    {
        $this->table || throw new DBException('No table set for request');
        $this->set   || throw new DBException('No values set for request');
    }
    
    protected function filter(): void {}
    
    protected function compile(): string
    {
        return $this->db->prepare(
            "UPDATE $this->table SET $this->set $this->where",
            array_merge(
                $this->table_data,
                $this->set_columns,
                $this->set_values,
                $this->where_data
            ),
            true
        );
    }
}
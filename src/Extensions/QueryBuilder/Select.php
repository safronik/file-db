<?php

namespace Safronik\DB\Extensions\QueryBuilder;

use Safronik\DB\DB;
use Safronik\DB\Exceptions\DBException;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Operations\Columns;
use Safronik\DB\Extensions\QueryBuilder\Operations\GroupBy;
use Safronik\DB\Extensions\QueryBuilder\Operations\Having;
use Safronik\DB\Extensions\QueryBuilder\Operations\Join;
use Safronik\DB\Extensions\QueryBuilder\Operations\Limit;
use Safronik\DB\Extensions\QueryBuilder\Operations\OrderBy;
use Safronik\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\DB\Extensions\QueryBuilder\Operations\Where;
use Safronik\DB\Extensions\QueryBuilder\Operations\With;

class Select extends BaseOperation
{
    use Table;
    use Columns;
    use Join;
    use Where;
    use GroupBy; // @todo implement
    use Having;  // @todo implement
    use OrderBy;
    use Limit;
    use With;
    
    public function __construct( string|array $table, DB $db )
    {
        $this->db = $db;
        
        foreach( (array) $table as $table_item ){
            $this->table( $table_item );
        }
    }
    
    /**
     * Returns count of entries considering the passed conditions
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count(): int
    {
        $this->limit = '';
        $this->columns = 'COUNT(*) as total';
        
        $this->db->setResponseMode( 'array' );
        
        return (int) $this->run()[0]['total'];
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
        $this->columns || $this->columns('*' );
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
        $query_string = "
                    $this->with
                    SELECT
                    $this->columns
                    $this->join_columns
                    FROM
                    $this->table
                    $this->joins
                    $this->group_by
                    $this->where
                    $this->having
                    $this->order_by
                    $this->limit";
        
        // Delete redundant spaces symbols
        $query_string = preg_replace( "@\s{2,}@", ' ', $query_string);
        
        // Replace placeholders with data
        return $this->db->prepare(
            $query_string,
            array_merge(
                $this->columns_data,
                $this->table_data,
                $this->join_data,
                $this->group_by_data,
                $this->where_data,
                $this->having_data,
                $this->order_by_data,
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
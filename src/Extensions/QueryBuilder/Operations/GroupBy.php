<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait GroupBy{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $group_by = '';
    private array  $group_by_data = [];
    
    public function groupBy( $condition )
    {
        $this->group_by = '';
        $this->group_by_data = [];
    }
}
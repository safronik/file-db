<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

use Safronik\DB\Extensions\QueryBuilder\Cte;

trait With{
    
    private string $with = '';
    
    public function with( Cte $cte )
    {
        $this->with = (string) $cte;
        
        return $this;
    }
}
<?php

namespace Safronik\DB\Extensions\QueryBuilder\Operations;

trait Ignore{

    private string $ignore = '';
    
    /**
     * Set IGNORE flag in SQL-request
     *
     * @param bool $ignore
     *
     * @return $this
     */
    public function ignore( bool $ignore = true ): static
    {
        $this->ignore = $ignore ? 'IGNORE' : '';
        
        return $this;
    }
}
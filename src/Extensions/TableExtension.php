<?php

namespace Safronik\DB\Extensions;

use Safronik\DB\Exceptions\DBException;

trait TableExtension{
	
    /**
     * Checks if the table exists
     *
     * @param $table string
     *
     * @return bool
     */
	public function isTableExists( string $table ): bool
    {
        return (bool) $this
            ->prepare(
                'SHOW TABLES LIKE :table_name;',
                [ ':table_name' => $table ]
            )
            ->query()
            ->fetchAll();
    }
    
    /**
     * Drops a table
     *
     * @param $table
     *
     * @return bool
     */
	public function dropTable( $table ): bool
    {
        return
            $this->isTableExists( $table ) &&
            ! $this->prepare(
                        'DROP TABLE :table',
                        [ ':table' => [ $table, 'table' ], ] )
                ->query()
                ->isTableExists( $table );
	}
    
    public function createTable( $table, array $columns, array $indexes, array $constraints, $if_not_exist = true ): bool
    {
        ! $if_not_exist && $this->isTableExists( $table ) &&
            throw new DBException( 'Table already exists: ' . $table);
        
        $sql_if_not_exist = $if_not_exist ? 'IF NOT EXISTS' : '';
        
                        $items[] = implode(",\n", $columns );
        $indexes     && $items[] = implode(",\n", $indexes);
        $constraints && $items[] = implode(",\n", $constraints);
        
        $items = implode( ",\n", $items );
        
        return $this
            ->prepare(
                "CREATE TABLE $sql_if_not_exist :table ( $items )"
                    // . ' ENGINE=MyISAM;', // @todo MyISAM is a temporary bug fix for https://jira.mariadb.org/browse/MDEV-24189
                ,[ ':table' => [ $table, 'table' ] ] )
            ->query()
            ->isTableExists( $table );
    }
    
    public function alterTable( string $table, array $columns = [], array $indexes = [], array $constraints = [] ): bool
    {
        $columns     && $changes[] = implode(",\n", $columns );
        $indexes     && $changes[] = implode(",\n", $indexes );
        $constraints && $changes[] = implode(",\n", $constraints );
        
        $changes = implode( ",\n", $changes );
        
        return $this
            ->prepare(
                "ALTER TABLE :table \n $changes;",
                [ ':table' => [ $table, 'table' ] ] )
            ->query()
            ->isTableExists( $table );
    }
}
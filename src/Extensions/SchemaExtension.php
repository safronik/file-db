<?php

namespace Safronik\DB\Extensions;

use Safronik\DB\Exceptions\DBException;

trait SchemaExtension{
    
    private static array $default_field = [
		'field'   => '',
		'type'    => '',
		'null'    => 'yes',
		'default' => '',
		'extra'   => '',
	];
    
    /**
     * Return all tables names with schema prefix added.
     * Wrapper for self::getBlogTableNames() and self::getCommonTableNames()
     *
     * @param bool $with_schema_prefix
     *
     * @return array
     */
    public static function getTableNames( $schema )
    {
        return array_keys( $schema );
    }

    public static function convertSchemaToSQLNotation( $schema )
    {
        return array_map(
            static function( $table_schema ){ return self::convertTableSchemaToSQLNotation( $table_schema ); },
            $schema
        );
    }
    
    public static function convertTableSchemaToSQLNotation( $table_schema )
    {
        return array_map(
            static function( $column_schema ){ return self::convertColumnSchemaToSQLNotation( $column_schema ); },
            $table_schema
        );
    }
    
    public static function convertColumnSchemaToSQLNotation( $column_schema )
    {
        $column_schema            = array_merge( self::$default_field, $column_schema );
        $column_schema['null']    = $column_schema['null'] === 'no' ? 'NOT NULL' : 'NULL';
        $column_schema['default'] = $column_schema['default'] ? 'DEFAULT ' . $column_schema['default'] : '';
        $column_schema['extra']   = $column_schema['extra'] ?: '';
        
        return $column_schema;
    }
    
    public static function convertTableSchemaFromSQLNotation( $table_schema )
    {
        return array_map(
            static function( $column_schema ){ return self::convertColumnSchemaFromSQLNotation( $column_schema ); },
            $table_schema
        );
    }
    
    public static function convertColumnSchemaFromSQLNotation( $column_schema )
    {
        $column_schema            = array_merge( self::$default_field, $column_schema );
        $column_schema['null']    = $column_schema['null'] === 'no' ? 'NOT NULL' : 'NULL';
        $column_schema['default'] = $column_schema['default'] ? 'DEFAULT ' . $column_schema['default'] : '';
        $column_schema['extra']   = $column_schema['extra'] ?: '';
        
        return $column_schema;
    }
    
    /**
	 * @param $table
	 * @param $column
	 *
	 * @return string|null
	 */
	public static function getColumnType( $schema, $table, $column ): ?string
    {
        $key = array_search( $column, array_column( $schema[ $table ]['columns'], 'field' ), true );
        
        ! $key &&
            throw new DBException( "Column not found: $table.$column");
        
        return self::convertSQLTypeToPHPType( $schema[ $table ]['columns']['type'] );
	}
    
    /**
     * @param $field_sgl_type
     *
     * @return string|null
     */
	public static function convertSQLTypeToPHPType( $field_sgl_type ): ?string
	{
        $field_sgl_type = strtolower( $field_sgl_type );
        return match (true) {
            in_array( $field_sgl_type, [ 'int', 'double', 'decimal', 'timestamp', ], true ) => 'int',
            in_array( $field_sgl_type, ['varchar', 'text', 'json', 'char', 'date', 'time', 'year', 'set', 'enum',], true ) => 'string',
            in_array( $field_sgl_type, [ 'float', ], true ) => 'float',
            
            default => throw new DBException('Unsupported type: ' . $field_sgl_type )
        };
	}
 
 
 
}
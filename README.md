<h1 align="center">safronik/db-warapper</h1>
<p align="center">
    <strong>A PHP library contains database wrapper to simplify it's usage</strong>
</p>

# About

This package contains wrapper for database, includes drivers for different databases:
- PDO
- ~~mysqli~~ (in development)
- Wordpress
- ~~Joomla~~ (in development)
- ~~Drupal~~ (in development)
- ...

There is a namespace "Extensions" contains extensions for this DB wrapper.
And few extensions to ease the programmer life:
- Query builder
- Operations with tables
- Placeholders for input data (works natively, you want see it) 
- Server side prepared extensions (works only for PDO)
- ~~SQL Schema~~ (in development)

# Installation

The preferred method of installation is via Composer. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require safronik/db-warapper
```
or just download files or clone repository (in this case you should bother about autoloader)

# Usage

## Creating a connection

If you are using PDO (you don't have any ready connection)

```php
$db = DB::getInstance(
    new \Safronik\DB\DBConfig([
        'driver'   => 'pdo',
        'username' => 'root',
        'password' => 'root',
        'hostname' => 'localhost', // or could be a container name if you are using Docker 
    ])
);
```

Existing PDO connection:
```php
global $some_pdo_connection_object; // should be an instanceof \PDO

$db = DB::getInstance(
    new \Safronik\DB\DBConfig([
        'driver'     => 'pdo',
        'connection' => $some_pdo_connection_object,
    ])
);
```

Because it's driver is PDO by default this should work too:

```php
global $some_pdo_connection_object; // should be an instanceof \PDO

$db = DB::getInstance(
    new \Safronik\DB\DBConfig([
        'connection' => $some_pdo_connection_object,
    ])
);
```

For WordPress:
```php
$global $wpdb;

$db = DB::getInstance(
    new \Safronik\DB\DBConfig([
        'driver'     => 'wpdb',
        'connection' => $wpdb,
    ])
);
```

## Raw query

```php
$rows_affected = $db->query( 'DELETE FROM some_table LIMIT 10' );
```

```php
$query_result = $db
    ->query( 'SELECT * FROM some_table' ) // Query already executed at this point
    ->fetchAll();                         // Fetching the result
```

## Query builder

Builder using a fluid (waterfall) interface

### Select

Methods allowed:
- table
- columns
- join (look for Join description below)
- ~~groupBy~~ (in development)
- ~~having~~ (in development)
- orderBy
- limit
- with (look for CTE description below)
- run

```php
$db
    ->select('users')
    ->orderBy('register_date', 'desc')
    ->limit(10)
    ->run();
```

### Insert

Methods allowed:
- columns
- ignore
- values
- onDuplicateKey
- run

```php
$values = [
    'some'    => 'thing',
    'another' => 'stuff',
]

$db
    ->insert( 'some_entity' )
    ->columns( array_keys( $values ) )
    ->values( $values )
    ->onDuplicateKey( 'update', $values )
    ->run();
```

### Update

Methods allowed:
- set
- where
- and
- or
- run

```php
$db
    ->update( 'options' )
    ->set( [ 'option_value' => $value ] )
    ->where([
        'option_name' => $option,
        'affiliation' => $group,
    ])
    ->and([ 'something' => 'different'])
    ->or( ['another' => 'example'])
    ->run()
```

### Delete

Methods allowed:
- where
- and
- or
- orderBy
- limit
- run

```php
$db
    ->update( 'options' )
    ->set( [ 'option_value' => $value ] )
    ->where([
        'option_name' => $option,
        'affiliation' => $group,
    ])
    ->run()
```

### Join (only as part of Select statement)

- Supports left, right and inner joins passed as the second argument
- Join operator supports <=> | != | > | < | >= | <= but it's not certain =D
- All columns from the joined table will be selected if no specified 

```php
$db
    ->select('users')
    ->join(
        [
            [ 'table_name_or_alias', 'id' ],
            '=', // <=> | != | > | < | >= | <=
            [ 'table_name_of_second_table_or_alias_2', 'some_id' ],
        ],
        'left',                                            // right | left | inner
        ['some_id', 'another_column', 'some_other_column'] // list of columns you want to join
    )
    ->limit(10)
    ->run();
```

### CTE (Common Table Expression)

When you call with() you should pass the $db->cte() inside

So the all 3 methods ( `cte()`, `anchor()` and `recursive()` ) should be called

`cte()` set the name of your common table expression. You can think that it's a table name.

`anchor()` is the root select expression

`recursive()` recursive expression

Any select expression could be any level of difficulty, using joins, orders and other

```php
$db
    ->select( 'cte' )
    ->with(
        $db
            ->cte( 'cte' )
            ->anchor( 'SELECT * FROM some_table WHERE id = 1' )
            ->recursive( '
                SELECT some_table.* 
                FROM some_table, cte
                WHERE some_table.parent = cte.id'
            )
        )
    )
    ->run();
```

You can also use a query builder in these `anchor` and `recursive` expressions

```php
$db
    ->select( 'cte' )
    ->with(
        $db
            ->cte( 'cte' )
            ->anchor(
                $db
                    ->select( $block_table )
                    ->where( [ 'id' => 1 ] )
            )
            ->recursive(
                $db
                    ->select( [ $block_table, 'cte' ])
                    ->columns( '*', $block_table )
                    ->where( [ [ [ $block_table, 'parent' ], '=', [ 'cte', 'id' ] ] ] )
        )
    )
    ->run();
```

## Tables operations

### Exists

Checks if the table exists

```php
$db->isTableExists( 'table_name');
```

### Drop

Drops a table

```php
$db->dropTable( 'table_name');
```

### Create

Some day I will add the documentation 

### Alter

Some day I will add the documentation 

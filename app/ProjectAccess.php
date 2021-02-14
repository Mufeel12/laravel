<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectAccess extends Model
{
    protected $table = 'project_access';

    protected $fillable = ['project_id', 'user_id'];

    public $timestamps = false;

    public static function insertOrUpdate(array $rows){
        $table = \DB::getTablePrefix().with(new self)->getTable();

        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                    array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::unprepared( $sql );
    }

    public static function bulkDelete(array $rows) {
        $table = \DB::getTablePrefix().with(new self)->getTable();
        $first = reset($rows);
        $keys = array_keys($first);

        $cond = implode(' ', array_map( function($item) use ($rows, $keys) {
            $values = implode( ',', array_map( function( $row ) {
                    return implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    );
                } , $rows )
            );
            return $keys[0] !== $item ? "AND {$item} IN ({$values})" : "{$item} IN ({$values})";
        }, $keys));

       $sql = "DELETE FROM {$table} WHERE {$cond}";

        return \DB::unprepared( $sql );
    }
}

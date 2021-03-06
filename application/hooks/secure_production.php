<?php
/**
 * This code ensure that current database is not production database in case that we are in debug mode.
 */
$actual_db_name = Kohana::config('database.default.connection.database');

if (app::in_debug_mode() AND ($actual_db_name == app::get_value('database_production_db_name')))
{
	$administrator_email = Kohana::config('wadmin.administrator_email');
	throw new WaAdmin_Exception('Production DB is set on test environment',
		"Production database is set on testing environment. Please contact administrator:
        <a href='mailto:{$administrator_email}'>{$administrator_email}</a>");
}
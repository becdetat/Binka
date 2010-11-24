<?php
/* Database
** Base class for database sources
** Contains some code from CakePHP's DboSource class
** BJS20090401
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

class Database extends Object {
	// Fields:
	// Configuration:
	var $host = null;
	var $login = null;
	var $password = null;
	var $database = null;
	var $port = null;
	var $tablePrefix = null;
	// Meta:
	var $columnTypes = array();
	// State:
	var $connected = false;
	
	
	// Override this to implement connecting to the database
	function connect() {}
	// Override this to implement disconnecting from the database
	function disconnect() {}
	
	// Override this with a method that returns the result of the given SQL query
	function query($sql) {}

	// Override this to implement selecting data from the database
	// SELECT $top $fields FROM $table WHERE $conditions GROUP BY $groupBy ORDER BY $orderBy
	function select($table, $fields=null, $conditions=null, $orderBy=null, $groupBy=null, $top=null) {}
	
	// Override this to implement updating data in the database
	// UPDATE $table SET ($data as x=y) WHERE $conditions
	function update($table, $data, $conditions) {}
	
	// override this to implement inserting data into the database
	// INSERT INTO $table($data keys) VALUES($data values)
	// return the new id
	function insert($table, $data) {}
	
	// Override this to implement deleting data from the database
	// DELETE FROM $table WHERE $conditions
	function delete($table, $conditions=null) {}
	
	// Override this to implement making the data safe for use in an SQL statement. Strings will be escaped and quoted.
	// Type is a string specifying the expected type (integer|float|boolean|text|string). If it is not supplied, the type is inferred
	// using $this->introspectType()
 	function makeValueSafe($data, $type = null) { return null; }
	
	// Overwrite this with a method that returns a schema structure for the given table
	function getTableSchema($tableName) { return null; }
	
	function getLastError() { return null; }

	
	// This is partly from CakePHP: Returns the type of the value in a string
	function introspectType($value) {
		if ($value === true || $value === false) {
			return 'boolean';
		}
		if (is_float($value) && floatval($value) === $value) {
			return 'float';
		}
		if (is_int($value) && intval($value) === $value) {
			return 'integer';
		}
		if (is_string($value) && strlen($value) > 255) {
			return 'text';
		}
		return 'string';
	}

}
?>
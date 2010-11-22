<?php
/* DbMySql
** Database implementation for MySQL databases
** BJS20090401
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

class DbMySql extends Database {
	// Fields:
	var $connection = null;
	var $columnTypes = array(
		'primary_key'	=> array('formatter' => 'intval'),
		'string'	=> array('limit' => '255'),
		'text'		=> array(),
		'integer'	=> array('limit' => '11', 'formatter' => 'intval'),
		'float'		=> array('formatter' => 'floatval'),
		'datetime'	=> array('format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
		'timestamp'	=> array('format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
		'time'		=> array('format' => 'H:i:s', 'formatter' => 'date'),
		'date'		=> array('format' => 'Y-m-d', 'formatter' => 'date'),
		'blob'		=> array(),
		'bool'		=> array('limit' => '1')
	);
	
	function connect() {
		if ($this->connected)
			$this->disconnect();
		
		// connect to the database
		$host = $this->host;
		if (isset($this->port)) {
			$host .= ':'.$this->port;
		}

		$this->connection = mysql_connect(
			$host,
			$this->login,
			$this->password,
			true);
		
		// select the database
		$this->connected = mysql_select_db($this->database, $this->connection);
		
		return $this->connected;
	}
	
	function disconnect() {
		$this->connected = !@mysql_close($this->connection);
	}
	
	// SELECT $fields FROM $table WHERE $conditions GROUP BY $groupBy ORDER BY $orderBy LIMIT $limit
	function select($table, $fields=null, $conditions=null, $orderBy=null, $groupBy=null, $limit=null) {
		if (empty($fields)) {
			$fields = '*';
		}
			
		// Form the SQL statement:
		$sql = 'SELECT ';
		// fields
		$sql .= $fields.' ';
		// FROM
		$sql .= 'FROM '.$this->tablePrefix.$table.' ';
		// WHERE
		if (!empty($conditions)) {
			$sql .= 'WHERE '.$conditions.' ';
		}
		// GROUP BY
		if (!empty($groupBy)) {
			$sql .= 'GROUP BY '.$groupBy.' ';
		}
		// ORDER BY
		if (!empty($orderBy)) {
			$sql .= 'ORDER BY '.$orderBy.' ';
		}
		// LIMIT
		if (!empty($limit)) {
			$sql .= 'LIMIT '.$limit;
		}

		return $this->query($sql);
	}
	
	function query($sql) {
		// execute the query and load it into $data
		$result = mysql_query($sql, $this->connection);
		if (!$result) {
			throw new Exception(mysql_error().': '.h($sql));
			//return mysql_error().': '.h($sql);
			//return 'Error in DbMySql.query()';
		}
		
		if (!strStartsWith(toUpper($sql), array('SELECT','SHOW'))) {
			// not a select operation, just return the result
			return $result;
		}
		
		// pull out the returned data
		$data = array();
		while ($row = mysql_fetch_assoc($result)) {
			$data[] = $row;
		}
		
		return $data;
	}
	
	// UPDATE $table SET ($data as x=y) WHERE $conditions
	// There is an assumption that data is already escaped
	function update($table, $data, $conditions) {
		$valueArray = array();
		foreach ($data as $key=>$value) {
			$valueArray[] = $key.'='.$value;
		}
			
		$sql = 'UPDATE '.$this->tablePrefix.$table.' SET '.implode(', ', $valueArray).' WHERE '.$conditions;

		// execute the query
		$result = mysql_query($sql, $this->connection);
		
		if (!$result) {
			throw new Exception(mysql_error().': '.h($sql));
		}
		
		return $result;
	}
	
	// INSERT INTO $table($data keys) VALUES($data values)
	// return the new id
	function insert($table, $data) {
		$sql = 
			'INSERT INTO '.$this->tablePrefix.$table.'('
			.implode(', ', array_keys($data))
			.') VALUES('
			.implode(', ', array_values($data))
			.')';

		$result = mysql_query($sql, $this->connection);
		if (!$result) {
			throw new Exception(mysql_error().': '.h($sql));
		}
		
		$this->id = mysql_insert_id($this->connection);
		
		return $this->id;
	}
	
	// DELETE FROM $table WHERE $conditions
	function delete($table, $conditions = null) {
		$sql = 'DELETE FROM '.$this->tablePrefix.$table;
		if (!empty($conditions)) {
			$sql .= ' WHERE '.$conditions;
		}
		$result = mysql_query($sql, $this->connection);
		if (!$result) {
			throw new Exception(mysql_error().': '.h($sql));
		}
		return $result;
	}
	
	// This is based on CakePHP's DboMySql::value():
	//   Returns a quoted and escaped string of $data for use in an SQL statement.
 	function makeValueSafe($data, $column = null) {
		if (empty($column)) {
			$column = $this->introspectType($data);
		}

		if ($data === null || (is_array($data) && empty($data))) {
			return 'NULL';
		} else if ($data === '' && $column !== 'integer' && $column !== 'float' && $column !== 'boolean') {
			return  "''";
		}
			
		if ($column == 'boolean') {
			if ($data === true || $data === false) {
				return ($data === true) ? 1 : 0;
			}
			return !empty($data) ? 1 : 0;
		} else if ($column == 'integer' || $column == 'float') {
			if ($data === '') {
				return 'NULL';
			}
			if (
					is_int($data) || 
					is_float($data) || 
					$data === '0' || 
					(is_numeric($data) && strpos($data, ',') === false && $data[0] != '0' && strpos($data, 'e') === false)
				) {
				return $data;
			}
		} else if ($column == 'blob') {
			return '0x'.bin2hex($data);
		}

		if (get_magic_quotes_gpc()) {
			$data = stripslashes($data);
		}
		
		return "'" . mysql_real_escape_string($data, $this->connection) . "'";
	}

	function getTableSchema($tableName) {
		$schema = array();
		$results = $this->query('SHOW COLUMNS FROM `'.$tableName.'`');
		foreach ($results as $row) {
			$schema[$row['Field']] = array();
			if ($row['Key'] == 'PRI') {
				$schema[$row['Field']]['type'] = 'primary_key';
			} else {
				$colType = str_replace(')','',$row['Type']);
				$colType = explode('(', $colType);
				if (count($colType) > 1) {
					$schema[$row['Field']]['limit'] = $colType[1];
				}
				$colType = $colType[0];
				switch ($colType) {
					case 'varchar':
					case 'char':
						$schema[$row['Field']]['type'] = 'string'; break;
					case 'text':
					case 'tinytext':
					case 'mediumtext':
					case 'longtext':
						$schema[$row['Field']]['type'] = 'text'; break;
					case 'tinyint':
					case 'smallint':
					case 'mediumint':
					case 'int':
					case 'bigint':
						$schema[$row['Field']]['type'] = 'integer'; break;
					case 'float':
					case 'double':
					case 'decimal':
						$schema[$row['Field']]['type'] = 'float'; break;
					case 'datetime':
						$schema[$row['Field']]['type'] = 'datetime'; break;
					case 'timestamp':
						$schema[$row['Field']]['type'] = 'timestamp'; break;
					case 'date':
						$schema[$row['Field']]['type'] = 'date'; break;
					case 'time':
						$schema[$row['Field']]['type'] = 'time'; break;
					case 'blob':
					case 'tinyblob':
					case 'mediumblob':
					case 'longblob':
						$schema[$row['Field']]['type'] = 'blob'; break;
					case 'bit':
					case 'bool':
						$schema[$row['Field']]['type'] = 'bit'; break;
					// not sure about these ones
					case 'year':
					case 'enum':
					case 'set':
					case 'binary':
					default:
						break;
				}
			}
			
			if (!empty($schema[$row['Field']]['type'])) {
				$schema[$row['Field']] = array_merge($this->columnTypes[$schema[$row['Field']]['type']], $schema[$row['Field']]);
			}
		}
		
		return $schema;
	}
	
	function getLastError() {
		return mysql_error($this->connection);
	}
}
?>
<?php
namespace Ali\DB;
use Exception;
use PDO;
use ReflectionClass;
class Connection extends PDO {
	// static variable to hold creds for database connections
	protected static $_creds              = null;
	protected static $_default_connection = null;
	/**
	 * this function sets up credentials database
	 *
	 * @param array $creds array sets of credentials
	 *        [default_connection] => 'connection1'
	 *        [connection]    => array
	 *                [connection1] => array
	 *                    [dns]      => 'localhost'
	 *                    *[user]     => 'root'
	 *                    *[password] => ''
	 *                    *[timezone] => 'America/Los_Angeles'
	 *
	 * @return boolean
	 */
	public static function setCredentials(array $creds) {
		// setting up pdo connection
		if (!isset($creds['connection'])) {
			throw new Exception(__class__.':Exception no connections_sets specified.', 0);
		}
		if (isset($creds['default_connection'])) {
			self::$_default_connection = $creds['default_connection'];
		}
		self::$_creds = $creds['connection'];
	}
	public static function getInstance($connection = false, $new = false, $replace = false) {
		// checking if we are using the default connection
		if ($connection === false) {
			$connection = self::$_default_connection;
		}
		// checking to see if connection already exists
		static $connections = array();
		if (isset($connections[$connection]) && !$new) {
			return $connections[$connection];
		}
		
		// getting connection creds
		if (!isset(self::$_creds[$connection])
			|| !isset(self::$_creds[$connection]['dns'])
		) {
			// throwing exception
			throw new Exception(__class__.':Exception dns not set', 1);
		}
		
		// building connection
		$params = array(self::$_creds[$connection]['dns']);
		if (isset(self::$_creds[$connection]['user'])) {
			$params[1] = self::$_creds[$connection]['user'];
			if (isset(self::$_creds[$connection]['password'])) {
				$params[2] = self::$_creds[$connection]['password'];
				$params[3] = array();
				if (isset(self::$_creds[$connection]['options'])) {
					$params[3] = self::$_creds[$connection]['options'];
				}
				if (isset(self::$_creds[$connection]['ssl_key'])) {
					$params[3][PDO::MYSQL_ATTR_SSL_KEY] = self::$_creds[$connection]['ssl_key'];
				}
				if (isset(self::$_creds[$connection]['ssl_cert'])) {
					$params[3][PDO::MYSQL_ATTR_SSL_CERT] = self::$_creds[$connection]['ssl_cert'];
				}
				if (isset(self::$_creds[$connection]['ssl_ca'])) {
					$params[3][PDO::MYSQL_ATTR_SSL_CA] = self::$_creds[$connection]['ssl_ca'];
				}
			}
		}

		$reflection = new ReflectionClass('\\Ali\\DB\\Connection');
		$db = $reflection->newInstanceArgs($params);
		
		// setting database timezone
		if (isset(self::$_creds[$connection]['timezone'])) {
			$timezone = self::$_creds[$connection]['timezone'];
			$db->query("SET time_zone = '{$timezone}'");
		}
		
		// setting database encoding
		if (isset(self::$_creds[$connection]['encoding'])) {
			$db->query('SET NAMES '.self::$_creds[$connection]['encoding']);
		}
		
		// saving and returning connection
		if (!isset($connections[$connection]) || $replace) {
			$connections[$connection] = $db;
		}
		return $db;
	}
	// setting up variables and functions for DB factory
	protected $_transaction = false;
	/**
	 * this function begins a transaction
	 *
	 * @return boolean
	 */
	public function beginTransaction() {
		if ($this->_transaction) {
			throw new Exception(__Class__.':Exception Transaction already thrown.',2);
		}
		$this->_transaction = true;
		return parent::beginTransaction();
	}
	/**
	 * this function commits transaction
	 *
	 * @return boolean
	 */
	public function commit() {
		$this->_transaction = false;
		return parent::commit();
	}
	/**
	 * this function rolls back a commit
	 *
	 * @return boolean
	 */
	public function rollBack(){
		$this->_transaction = false;
		return parent::rollBack();
	}
	/**
	 * this function fetches all rows that match the given query and args
	 *
	 * @param string $sql  MySQL query statment
	 * @param array  $args array holding queryPrepared parameters
	 *
	 * @return array|false
	 */
	public function fetchAll($sql, array $args = array()){
		return $this->_fetch('all', $sql, $args);
	}
	/**
	 * this function fetches a single column that matches the given query and args
	 *
	 * @param string $sql  MySQL query string
	 * @param array  $args array holding queryPrepared parameters
	 *
	 * @return array|false
	 */
	public function fetchColumn($sql, array $args = array()){
		return $this->_fetch('column', $sql, $args);
	}
	/**
	 * this function returns a single row that match the given query and args
	 *
	 * @param string $sql  MySQL query string
	 * @param array  $args array holding queryPrepared parameters
	 *
	 * @return array|false
	 */
	public function fetchRow($sql, array $args = array()){
		return $this->_fetch('row', $sql, $args);
	}
	/**
	 * this function returns a single value that match the given query and args
	 *
	 * @param string $sql  MySQL query string
	 * @param string $args array holding queryPrepared parameters
	 *
	 * @return mixed|false
	 */
	public function fetchOne($sql, array $args = array()){
		return $this->_fetch('one', $sql, $args);
	}
	/**
	 * this function returns a key value array where first column is the key and the second column is the value
	 *
	 * @param string $sql  MySQL query string
	 * @param string $args array holding queryPrepared parameters
	 *
	 * @return mixed|false
	 */
	public function fetchKeyValue($sql, array $args = array()) {
		return $this->_fetch('keyvalue', $sql, $args);
	}
	/**
	 * this function returns a key row array where the first column is the key and the rest are fetched as an array
	 *
	 * @param string $sql  MySQL query string
	 * @param string $args array holding queryPrepared parameters
	 *
	 * @return mixed|false
	 */
	public function fetchGroupBy($sql, array $args = array()) {
		return $this->_fetch('groupby', $sql, $args);
	}
	/**
	 * this function returns all rows, a single column, a single row or a single value
	 * that match the given query and args
	 *
	 * @param string $type type of result requested all|column|row|one
	 * @param string $sql  MySQL query string
	 * @param string $args array holding queryPrepared parameters
	 *
	 * @return array|mixed|false
	 */
	private function _fetch($type, $sql, array $args = array()) {
		// preparing statment
		$stmt = $this->prepare($sql);
		//\Ali\Log::put(array('sql' => $sql, 'args' => $args), 'database', 'query');
		if ($stmt === false) {
			throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 3);
		}
		// executing statment
		if ($stmt->execute($args)) {
			if ($type === 'all') {
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			} else if ($type === 'row') {
				return $stmt->fetch(PDO::FETCH_ASSOC);
			} else if ($type === 'column') {
				return $stmt->fetchAll(PDO::FETCH_COLUMN);
			} else if ($type === 'one') {
				return $stmt->fetch(PDO::FETCH_COLUMN);
			} else if ($type === 'keyvalue') {
				return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
			} else if ($type === 'groupby') {
				return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
			} else {
				throw new Exception(__class__.':Exception on query type.', 4);
			}
		}
		// execution of statment failed
		throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 5);
	}
	public function getLock($name, $timeout = 10) {
		// building sql statment
		$sql = "GET_LOCK(?,?)";
		return $this->_fetch('one', $sql, array($name, $timeout));
	}
	public function releaseLock($name) {
		// building sql statement
		$sql = "RELEASE_LOCK(?)";
		return $this->_fetch('one', $sql, array($name));
	}
	/**
	 * this function lets you insert a single row
	 *
	 * @param string $table table name
	 * @param array  $data  array of associative row data.
	 *
	 * @return int|false
	 */
	public function insert($table, array $data) {
		// building out args array
		$values  = array_values($data);
		$columns = array_keys($data);
		$args    = array();
		$holders = array();
		foreach ($values as $key => $value) {
			if ($value instanceOf Expression) {
				$holders[] = $value->getExpression();
				$args      = array_merge($args, $value->getParams());
			} else {
				$holders[]         = ':adb'.$key;   
				$args[':adb'.$key] = $value;
			}
		}
		// building out values
		// building query
		$sql = "
		INSERT INTO {$table} 
		(`".implode('`,`', $columns)."`) 
		VALUES
		(".implode(', ', $holders).")";
		// creating statment
		$stmt = $this->prepare($sql);
		//\Ali\Log::put(array('sql' => $sql, 'args' => $args), 'database', 'query');
		if ($stmt === false) {
			throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 6);
		}
		
		// executing statment
		if ($stmt->execute($args)) {
			// returning last inserted id;
			return $this->lastInsertId();
		}
		// execution of statment failed
		throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 7);
	}
	/**
	 * this function lets you insert a multiple rows
	 *
	 * @param string $table table name
	 * @param array  $data  array of associative row data.
	 * @param string $extra only use if you know what you are doing  
	 *
	 * @return int|false
	 */
	/*
	public function multiInsert($table, array $data, $extra = '') {
		// checking for data to insert
		if (empty($data)) {
			return 0;
		}

		// building out args array
		$values  = array_values($data);
		$columns = array_keys($data);
		$args    = array();
		$holders = array();
		foreach ($values as $key => $value) {
			if ($value instanceOf Expression) {
				$holders[] = $value->getExpression();
				$args      = array_merge($args, $value->getParams());
			} else {
				$holders[]         = 'adb'.$key;   
				$args[':adb'.$key] = $value;
			}
		}
		// separating data array
		$columns = array_keys($data[0]);
		$args    = array();
		foreach ($data as $row) {
			$args = array_merge($args, array_values($row));
		}
		// building args string
		$args_string = "(?".str_repeat(',?', count($columns)-1).")";
		// building query
		$sql = "
		INSERT INTO {$table} 
		(`".implode('`,`', $columns)."`)
		VALUES
		{$args_string}".str_repeat(",{$args_string}", count($data)-1).' '.$extra;
		// creating statment
		$stmt = $this->prepare($sql);
		if ($stmt === false) {
			throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 8);
		}
		// executing statment
		if ($stmt->execute($args)) {
			// returning last inserted id;
			return $this->lastInsertId();
		}
		// execution of statment failed
		throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 9);
	}
	*/
	/**
	 * this function lets you update a given row
	 *
	 * @param string $table       table name
	 * @param array  $data        array of data to update the given row
	 * @param mixed  $condition   id of the primary key
	 *
	 * @return boolean
	 */
	public function update($table, array $data, $condition) {
		// building out args array
		$args    = array();
		$sets    = array();
		$i       = 0;
		foreach ($data as $key => $value) {
			if ($value instanceOf Expression) {
				$sets[] = "`{$key}`=".$value->getExpression();
				$args   = array_merge($args, $value->getParams());
			} else {
				$sets[]            = "`{$key}`=:adb{$i}";
				$args[':adb'.$i] = $value;
			}
			$i++;
		}
		// setting up key
		if ($condition instanceof Expression) {
			$expression = $condition->getExpression();
			$args       = array_merge($args, $condition->getParams());
		} else if (!is_array($condition)) {
			$expression      = "`id`=:adb{$i}";
			$args[':adb'.$i] = $condition;
		} else if (is_array($condition)) {
			$conditions = array();
			foreach ($condition as $key => $value) {
				if ($value instanceOf Expression) {
					$conditions[] = "`{$key}`=".$value->getExpression();
					$args         = array_merge($args, $value->getParams());
				} else {
					$conditions[]    = "`{$key}`=:adb{$i}";
					$args[':adb'.$i] = $value;
				}
				$i++;
			}
			$expression = implode(' AND ', $conditions);
		} else {
			throw new Exception(__class__.':Exception Invalid Condition', 10);
		}
		// building query
		$sets = implode(', ', $sets);
		$sql  = "UPDATE {$table}
		SET {$sets}
		WHERE {$expression}";
		// creating statment
		$stmt = $this->prepare($sql);
		//\Ali\Log::put(array('sql' => $sql, 'args' => $args), 'database', 'query');
		if ($stmt === false) {
			throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 11);
		}
		// executing statment
		if ($stmt->execute($args)) {
			return true;
		}
		// execution of statment failed
		throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 12);
	}
	/**
	 * this function lets you delete any row from a given table knowing the rows primary key
	 *
	 * @param string $table     table name
	 * @param mixed  $condition primary key value
	 *
	 * @return boolean
	 */
	public function delete($table, $condition) {
		// setting up conditions to delete on
		if ($condition instanceof Expression) {
			$expression = $condition->getExpression();
			$args       = array_merge($args, $condition->getParams());
		} else if (!is_array($condition)) {
			$expression      = "`id`=:adb{$i}";
			$args[':adb'.$i] = $condition;
		} else if (is_array($condition)) {
			$conditions = array();
			$i          = 0;
			foreach ($condition as $key => $value) {
				if ($value instanceOf Expression) {
					$conditions[] = "`{$key}`=".$value->getExpression();
					$args         = array_merge($args, $value->getParams());
				} else {
					$conditions[]    = "`{$key}`=:adb{$i}";
					$args[':adb'.$i] = $value;
				}
				$i++;
			}
			$expression = implode(' AND ', $conditions);
		} else {
			throw new Exception(__class__.':Exception Invalid Condition', 10);
		}
		// creating sql
		$sql = "DELETE FROM {$table} WHERE {$expression}";
		$args = array($row_id);
		// creating statment
		$stmt = $this->prepare($sql);
		//\Ali\Log::put(array('sql' => $sql, 'args' => $args), 'database', 'query');
		if ($stmt === false) {
			throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 13);
		}
		// executing statment
		if ($stmt->execute($args)) {
			return true;
		}
		// execution of statment failed
		throw new Exception(__class__.':Exception '.implode(' ', $stmt->errorInfo()), 14);
	}
}
?>
<?php
namespace Ali\DB;

use Exception;
use Ali\DB\Connection;
use Ali\DB\Expression;

abstract class ActiveRecord {
	// this function returns an instance of the class
	public static function getInstance() {
		$class = get_called_class();
		return new $class();
	}

	// database info
	protected $_db;
	protected $_table;
	protected $_alias;

	// record info
	protected $_is_new;
	protected $_data;
	protected $_original_data;
	protected $_attributes;
	protected $_indexes;
	protected $_auto_incremented;

	/**
	 * this constructor sets up the active record object
	 *
	 * @param Connection $db    database connection
	 * @param string     $table table in database
	 *
	 * @return void
	 */
	public function __construct(Connection $db, $table) {
		// setting database and table
		$this->_db     = $db;
		$this->_table  = $table;
		$this->_is_new = true;
		$this->_alias  = 't';

		// getting info about table
		$table_info              = self::getTableData($db, $table);
		$this->_data             = $table_info['data'];
		$this->_attributes       = $table_info['attributes'];
		$this->_indexes          = $table_info['indexes'];
		$this->_auto_incremented = $table_info['auto_incremented'];

		// saving original data
		$this->_original_data = $this->_data;
	}
	/**
	 * this function returns all kinds of table information
	 * it is writen in such a way that it only queries for the data for
	 * a given table once.
	 *
	 * @param Connection $db    database connection
	 * @param string     $table table in database
	 *
	 * @return array
	 */
	public static function getTableData(Connection $db, $table) {
		// init table data cache
		static $data_cache = array();
		if (isset($data_cache[$table])) {
			return $data_cache[$table];
		}

		// getting info about table
		$table_info = $db->fetchAll("DESCRIBE ".$table);
		if ($table_info === false || empty($table_info)) {
			throw new Exception("Table [{$table}] cannot be described.");
		}
		
		// parsing table description
		$info = array(
			'auto_incremented' => false
		);
		foreach ($table_info as $column) {
			if ($column['Default'] === 'CURRENT_TIMESTAMP') {
				$info['data'][$column['Field']] = new Expression('NOW()');
			} else {
				$info['data'][$column['Field']] = $column['Default'];
			}
			if ($column['Extra'] == 'auto_increment') {
				$info['auto_incremented'] = $column['Field'];
			}
			$info['attributes'][$column['Field']] = $column;
		}

		// getting key info on table
		$key_info = $db->fetchAll("SHOW INDEX FROM ".$table);
		if ($key_info === false || empty($key_info)) {
			throw new Exception("Table [{$table}] connot show index.");
		}

		// parsing table indexes
		foreach ($key_info as $column) {
			$info['indexes'][$column['Key_name']][$column['Seq_in_index']] = $column['Column_name'];
		}

		// caching data and returning
		$data_cache[$table] = $info;
		return $info;
	}
	/**
	 * this function returns the table name that is being used for the active record
	 *
	 * @return string
	 */
	public function tableName() {
		return $this->_table;
	}
	/**
	 * this function returns an object
	 *
	 * @param mixed $pk non array: will be compared with tables pk column
	 *                  array    : keys are columns and values will be compared
	 *
	 * @return ActiveRecord
	 */
	public function findByPK($pk) {
		// if not array assume id is pk
		if (!is_array($pk)) {
			if (count($this->_indexes['PRIMARY']) === 1) {
				$pk = array(
					$this->_indexes['PRIMARY'][1] => $pk
				);
			} else {
				throw new Exception('Cannot use single value to a multiple column primary key');
			}
		}
		// chcking if array is empty
		if (empty($pk)) {
			throw new Exception('No primary key given.');
		}
		// building sql
		$sql = "
		SELECT *
		FROM {$this->_table}
		WHERE ".implode(' = ? AND ', array_keys($pk)).' = ?
		LIMIT 1';
		$data = $this->_db->fetchRow($sql, array_values($pk));
		if ($data === false) {
			return false;
		}
		return $this->loadFromArray($data);
	}
	public function findAll(Expression $expression = null) {
		$sql = "SELECT 
			*
		FROM {$this->_table}";
		if ($expression === null) {
			$rows = $this->_db->fetchAll($sql);
		} else {
			$sql .= " WHERE ".$expression->getExpression();
			$rows = $this->_db->fetchAll($sql, $expression->getParams());
		}
		$result = array();
		foreach ($rows as $row) {
			$result[] = self::getInstance()->loadFromArray($row);
		}
		return $result;
	}
	public function loadFromArray(array $attributes) {
		$this->_is_new = false;
		$this->_data   = $attributes;
		$this->_data   = $attributes;
		$this->_afterLoad();
		return $this;
	}
	/**
	 * this function is called after a record is loaded
	 *
	 * @return void
	 */
	protected function _afterLoad() {
	}
	/**
	 * this function is a dynamic getter, and is designed
	 * to allow getting of table row data via public attributes
	 * it is not to be called by itself it is a magic method
	 *
	 * @param string $key table column name
	 *
	 * @return mixed attribute value
	 */
	public function __get($key) {
		if (in_array($key, array_keys($this->_data))) {
			return $this->_data[$key];
		}
		throw new Exception("Attribute [{$key}] Does Not Exist.");
	}
	public function __set($key, $value) {
		if (in_array($key, array_keys($this->_data))) {
			$this->_data[$key] = $value;
			return;
		}
		throw new Exception("Attribute [{$key}] Does Not Exist.");
	}
	public function getAttributeLabel($field) {
		$attributes = $this->getAttributeLabels();
		if (isset($attributes[$field])) {
			return $attributes[$field];
		}
		return ucwords(str_replace('_', ' ', $field));
	}
	public function getAttributeLabels() {
		return array();
	}
	public function getEnumLabel($field, $value = null) {
		$options = $this->getEnumOptions($field);
		if ($value === null) {
			$value = $this->$field;
		}
		if (!isset($options[$value])) {
			throw new Exception('No such value in enum.');
		}
		return $options[$value];
	}
	public function getEnumOptions($field) {
		// parcing enum type for possible values
		if (!isset($this->_attributes[$field])) {
			throw new Exception('No such field exists: '.$field);
		}
		// checking for enum string
		$enum_string = $this->_attributes[$field]['Type'];
		if (strncmp($enum_string, 'enum', 4) !== 0) {
			throw new Exception('No such enum field exists: '.$field);
		}
		// building enum options
		$enum_options = array();
		$enums        = explode("','", substr($enum_string, 6, -2));
		$labels       = $this->getEnumLabels($field);
		foreach ($enums as $enum) {
			if (isset($labels[$enum])) {
				$enum_options[$enum] = $labels[$enum];
			} else {
				$enum_options[$enum] = ucwords(str_replace('_', ' ', $enum));
			}
		}
		return $enum_options;
	}
	public function getEnumLabels($field) {
		return array();
	}
	protected function _beforeSave() {
		return true;
	}
	public function save() {
		// invoking before save
		$check = $this->_beforeSave();
		if ($check === false || is_array($check)) {
			return $check;
		}
		// validating data based on database types
		$check = $this->_validateData();
		if (is_array($check)) {
			return $check;
		}
		// calculating changes to be made
		$changes = array();
		foreach ($this->_data as $key => $value) {
			if ($this->_original_data[$key] !== $value) {
				$changes[$key] = $value;
			}
		}

		// saving record
		if ($this->_is_new) {
			// Insert
			$check = $this->_db->insert($this->_table, $changes);
			if ($check === false) {
				return array(
					'error' => "Error: Unknown on save [{$this->_table}] insert."
				);
			}
			// setting auto_incremented column if exists
			if ($this->_auto_incremented !== false) {
				$this->_data[$this->_auto_incremented] = $check;
			}
		} else {
			if (!empty($changes)) {
				// getting primary key info
				$pk = array();
				foreach ($this->_indexes['PRIMARY'] as $field) {
					$pk[$field] = $this->_original_data[$field];
				}
				// Update
				$check = $this->_db->update($this->_table, $changes, $pk);
				if ($check === false) {
					return array(
						'error' => "Error: Unknown on save [$this->_table] update."
					);
				}
			}
		}

		// getting new PK
		$pk = array();
		foreach ($this->_indexes['PRIMARY'] as $field) {
			$pk[$field] = $this->_data[$field];
		}
		// invoking after save
		$this->_afterSave();
		// updating object with database values
		$this->findByPK($pk);
		return true;
	}
	protected function _afterSave() {
	}
	private function _validateData() {
		// init errors array
		$errors = array();

		// building out int array
		$int_array = array(
			'tinyint'   => array(
				'signed'   => array('min' => -128,                 'max' => 127),
				'unsigned' => array('min' => 0,                    'max' => 255)
			),
			'smallint'  => array(
				'signed'   => array('min' => -32768,               'max' => 32767),
				'unsigned' => array('min' => 0,                    'max' => 65535)
			),
			'mediumint' => array(
				'signed'   => array('min' => -8388608,             'max' => 8388607),
				'unsigned' => array('min' => 0,                    'max' => 16777215)
			),
			'int'       => array(
				'signed'   => array('min' => -2147483648,          'max' => 2147483647),
				'unsigned' => array('min' => 0,                    'max' => 4294967295)
			),
			'bigint'    => array(
				'signed'   => array('min' => -9223372036854775808, 'max' => 9223372036854775807),
				'unsigned' => array('min' => 0,                    'max' => 18446744073709551615)
			)
		);

		// building out text array
		$text_array = array(
			'tinytext'   => 255,
			'text'       => 65535,
			'mediumtext' => 16777215,
			'longtext'   => 4294967295
		);

		// loop through to find issues
		foreach ($this->_data as $field => $value) {
			if ($value instanceof Expression) {
				// ignore expressions we cannot validate
				continue;
			}
			// validating based on mysql field type
			$raw_type    = $this->_attributes[$field]['Type'];
			$field_type  = '';
			$field_param = '';
			$field_extra = '';
			$appending   = 'field_type';
			$len         = strlen($raw_type);
			for ($i = 0; $i<$len; $i++) {
				if ($raw_type{$i} == '(') {
					$appending = 'field_param';
					continue;
				}
				if ($raw_type{$i} == ')') {
					$appending = 'field_extra';
					continue;
				}
				$$appending .= $raw_type{$i};
			}
			$field_type  = trim($field_type);
			$field_param = trim($field_param);
			$field_extra = trim($field_extra);

			// checking for various int fields
			if (isset($int_array[$field_type][$field_extra])) {
				if ($value > $int_array[$field_type][$field_extra]['max']) {
					$errors[$field][] = 'Value cannot be larger than '.$int_array[$field_type][$field_extra]['max'];
				} else if ($value < $int_array[$field_type][$field_extra]['min']) {
					$errors[$field][] = 'Value cannot be smaller than '.$int_array[$field_type][$field_extra]['min'];
				}
				continue;
			}

			// checking for date
			if ($field_type === 'date') {
				if (!preg_match('/\d\d\d\d-\d\d-\d\d/', $value)) {
					$errors[$field][] = 'Value is not valid date.';
				}
				continue;
			}

			// checking for timestamp
			if ($field_type === 'datetime' || $field_type === 'timestamp') {
				if (!preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $value)
					&& strtoupper($value) != 'CURRENT_TIMESTAMP'
				) {
					$errors[$field][] = 'Value is not a valid timestamp.';
				}
				continue;
			}

			// chcking for a varchar
			if ($field_type === 'varchar') {
				if (strlen($value) > $field_param) {
					$errors[$field][] = "Value cannot be longer than {$field_param} characters long.";
				}
				continue;
			}

			// checking for various text fields
			if (isset($text_array[$field])) {
				if (strlen($value) > $text_array[$field]) {
					$errors[$field][] = "Value cannot be longer than {$text_array[$field_type]} characters long.";
				}
				continue;
			}

			// checking for enumerated types
			if ($field_type === 'enum') {
				$options = explode(',', $field_param);
				if (!in_array("'".$value."'", $options)) {
					$errors[$field][] = 'Value is not supported.';
				}
				continue;
			}
		}

		// returning errors
		if (!empty($errors)) {
			return array(
				'errors' => $errors
			);
		}
		return true;
	}
}
?>
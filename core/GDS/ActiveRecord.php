<?php
namespace Ali\GDS;
use Exception;
use Ali\DB\Criteria;
use GDS\Entity;
use GDS\Store;
use GDS\Schema;

abstract class ActiveRecord extends Entity {

	private $_errors = array();

	// this function returns an instance of the class
	public static function getInstance() {
		$class = get_called_class();
		return new $class();
	}

	public function __construct() {
		$this->_initValueDefaults();
	}	

	public function isNew() {
		return $this->getKeyId() === null;
	}
	public abstract function getDefinition();

	public function addError($field, $errors) {
		if (!isset($this->_errors[$field])) {
			$this->_errors[$field] = array();
		}
		if (is_array($errors)) {
			$this->_errors[$field] = array_merge($this->_errors[$field], $errors);
		} else {
			$this->_errors[$field][] = $errors;
		}
	}

	public function getErrors($field = null) {
		if ($field === null) {
			return $this->_errors;
		}
		if (!isset($this->_errors[$field])) {
			return false;
		}
		return $this->_errors[$field];
	}

	private function _initValueDefaults() {
		$def = $this->getDefinition();
		foreach ($def as $key => $row_def) {
			if (isset($row_def['default'])) {
				$this->$key = $row_def['default'];
			}
		}
	}
	public function onBeforeValidate() {
		return true;
	}
	private function _validate() {
		$def   = $this->getDefinition();
		$valid = true;
		foreach ($def as $key => $value) {
			if (isset($value['validators'])) {
				foreach ($value['validators'] as $validator) {
					$validator->filter($this, $key);
				}
				foreach ($value['validators'] as $validator) {
					if ($validator->validate($this, $key) === false) {
						$this->addError($key, $validator->getErrors());
						$valid = false;
					}
				}
			}
		}
		return $valid;
	}
	public function onAfterValidate() {

	}
	public function getClass() {
		return get_class($this);
	}
	public function getStore() {
		static $store = false;
		if ($store === false) {
			$store = new Store($this->getSchema());
		}
		return $store;
	}
	public function getSchema() {
		static $schema = false;
		if ($schema === false) {
			$schema     = new Schema($this->getKind());
			$schema_def = $this->getDefinition();
			foreach ($schema_def as $name => $field) {
				if (!isset($field['type'])) {
					throw new Exception('Error, type not set in field deffinition');
				}
				$func  = 'add'.ucwords($field['type']);
				$index = isset($field['index']) ? $field['index'] : false;
				$schema->$func($name, $index);
				$schema->setEntityClass($this->getClass());
			}
		}
		return $schema;
	}
	public function findByPk($pk) {
		return $this->getStore()->fetchById($pk);
	}
	public function find(Criteria $criteria = null) {
		if ($criteria == null) {
			$criteria = new Criteria();
		}
		return $this->getStore()->fetchOne(
			$criteria->getQuery($this->getKind()),
			$criteria->params
		);
	}
	public function findByAttributes($attributes) {
		$criteria = new Criteria();
		$i = 0;
		foreach ($attributes as $key => $value) {
			$param = 'fba_'.$i;
			$criteria->addCondition($key.' = @'.$param);
			$criteria->params[$param] = $value;
		}
		return $this->find($criteria);	
	}
	public function findAll(Criteria $criteria = null) {
		if ($criteria == null) {
			$criteria = new Criteria();
		}
		return $this->getStore()->fetchAll(
			$criteria->getQuery($this->getKind()),
			$criteria->params
		);
	}
	public function findAllByAttributes($attributes) {
		$criteria = new Criteria();
		$i = 0;
		foreach ($attributes as $key => $value) {
			$param = 'fba_'.$i;
			$criteria->addCondition($key.' = @'.$param);
			$criteria->params[$param] = $value;
		}
		return $this->findAll($criteria);	
	}
	public function onBeforeSave() {
		return true;
	}
	public function save() {
		$valid = false;
		if ($this->onBeforeValidate()) {
			$valid = $this->_validate();
			$this->onAfterValidate();

			if ($valid && $this->onBeforeSave()) {
				$this->getStore()->upsert($this);
				$this->onAfterSave();
			}
		}
		return $valid;
	}
	public function onAfterSave() {
		// place holder function
	}
	public function __get($key) {
		if (isset($this->$key)) {
			return parent::__get($key);
		}
		throw new Exception('Error '.$key.' is not set.');
	}
	public function __set($key, $value) {
		$definition = $this->getDefinition();
		if (isset($definition[$key])) {
			parent::__set($key, $value);
		} else {
			throw new Exception('Error '.$key.' is not settable.');
		}
	}
	public function getAttributeLabel($attribute) {
		$definition = $this->getDefinition();
		if (isset($definition[$attribute])) {
			if (isset($definition[$attribute]['label'])) {
				return $definition[$attribute]['label'];
			}
		}
		return ucwords(str_replace('_', ' ', $attribute));
	}
}
<?php
namespace Ali\DB;
use Exception;
use GDS\Entity;
use GDS\Store;
use GDS\Schema;

abstract class ActiveRecordGDS extends Entity implements ActiveRecordInterface {
	use Relation;
	private $_errors = array();

	// this function returns an instance of the class
	public static function getInstance() {
		$class = get_called_class();
		return new $class();
	}

	public function __construct() {
		$this->_initValueDefaults();
	}

	public function setAttributes(array $attributes, array $safe = []) {
		if (empty($safe)) {
			$safe = array_keys($attributes);
		}
		foreach ($safe as $safe_key) {
			if (isset($attributes[$safe_key]) || empty($safe)) {
				$this->$safe_key = $attributes[$safe_key];
			}
		}
	}

	public function labels() {
		return [];
	}

	public function validators() {
		return [];
	}

	public abstract function getDefinition();

	public function getPK() {
		return $this->getKeyId();
	}

	public function isNew() {
		return $this->getPK() === null;
	}

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
			$this->$key = null;
			if (is_array($row_def) && isset($row_def['default'])) {
				$this->$key = $row_def['default'];
			}
		}
	}

	public function _beforeValidate() {
		return true;
	}

	public function validate() {
		$validators = $this->validators();
		foreach ($validators as $key => $validator) {
			$validator->filter($this, $key);
		}
		foreach ($validators as $key => $validator) {
			if ($validator->validate($this, $key) === false) {
				$this->addError($key, $validator->getErrors());
				$validator->clearErrors();
			}
		}
		return empty($this->getErrors());

	}
	public function _afterValidate() {

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
				if (!is_array($field)) {
					$field = ['type' => $field];
				}
				if (!isset($field['type'])) {
					throw new Exception('Error, type not set in field deffinition');
				}
				$func  = 'add'.ucwords($field['type']);
				$index = isset($field['index']) ? $field['index'] : false;
				$schema->$func($name, $index);
				$schema->setEntityClass(get_called_class());
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
	public function findByAttributes(array $attributes) {
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
	public function findAllByAttributes(array $attributes) {
		$criteria = new Criteria();
		$i = 0;
		foreach ($attributes as $key => $value) {
			$param = 'fba_'.$i;
			$criteria->addCondition($key.' = @'.$param);
			$criteria->params[$param] = $value;
		}
		return $this->findAll($criteria);	
	}
	protected function _beforeSave() {
		return empty($this->getErrors());
	}
	public function save() {
		$valid = false;
		if ($this->_beforeValidate()) {
			$valid = $this->validate();
			$this->_afterValidate();

			if ($valid && $this->_beforeSave()) {
				$this->getStore()->upsert($this);
				$this->_afterSave();
			}
		}
		return $valid;
	}
	public function _afterSave() {
		// place holder function
	}
	public function __get($key) {
		// checking entity attributes
		$definition = $this->getDefinition();
		if (isset($definition[$key])) {
			return parent::__get($key);
		}
		// checking relations
		if ($this->issetRelation($key)) {
			return $this->getRelation($key);
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
	// public function __isset($key) {
	// 	return parent::__isset($key);
	// }
	// public function __unset($key) {
	// 	return parent::__unset($key);
	// }
	public function getAttributeLabel($attribute) {
		$labels = $this->labels();
		if (isset($labels[$attribute])) {
			if (isset($labels[$attribute])) {
				return $labels[$attribute];
			}
		}
		return ucwords(str_replace('_', ' ', $attribute));
	}
	public function getAttributes() {
		return $this->getData();
	}
	public function relations() {
		return [];
	}
	public function delete() {
		//todo
	}
}
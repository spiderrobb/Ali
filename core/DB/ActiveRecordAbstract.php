<?php
namespace Ali\DB;

abstract class ActiveRecordAbstract implements ActiveRecordInterface {
	private $_errors = array();
	public static function getInstance() {
		$class = get_called_class();
		return new $class();
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
	protected function _beforeValidate() {
		return true;
	}
	protected function _afterValidate() {
	}
	protected function _beforeSave() {
		return true;
	}
	protected function _afterSave() {
	}
	public function save() {
		$valid = false;
		if ($this->_beforeValidate()) {
			$valid = $this->_validate();
			$this->_afterValidate();

			if ($valid && $this->_beforeSave()) {
				$this->_persist();
				$this->_afterSave();
			}
		}
		return $valid;
	}
	protected abstract function _persist();
}
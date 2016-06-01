<?php
namespace Ali\Base;

abstract class ValidatorAbstract implements ValidatorInterface {
	public static function getInstance(array $params = array()) {
		$class  = get_called_class();
		$object = new $class(); 
		foreach ($params as $key => $value) {
			$object->$key = $value;
		}
		return $object;
	}
	protected $errors = array();
	public function addError($error) {
		$this->errors[] = $error;
	}
	public function getErrors() {
		return $this->errors;
	}
	public function filter($object, $attribute) {}
	public abstract function validate($object, $attribute);
}
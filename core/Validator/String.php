<?php
namespace Ali\Validator;
use Exception;
use Ali\Base\ValidatorAbstract;
class String extends ValidatorAbstract {
	public $min_length = 0;
	public $max_length = null;
	public $trim       = true;
	public $case       = null;
	public function filter($object, $attribute) {
		$value = $object->$attribute;
		if ($this->trim && is_string($value)) {
			$value = trim($value);
		}
		if ($this->case !== null) {
			if ($this->case === 'upper') {
				$value = strtoupper($value);
			} else if ($this->case === 'lower') {
				$value = strtolower($value);
			} else {
				throw new Exception("Error: case:{$this->case} is not a valid case.");
			}
		}
		$object->$attribute = $value;
	}
	public function validate($object, $attribute) {
		$value      = $object->$attribute;
		$str_length = strlen($value);
		$min_length = $str_length > $this->min_length;
		$max_length = $this->max_length === null || $str_length < $this->max_length;
		if (!$min_length || !$max_length) {
			$label = $object->getAttributeLabel($attribute);
			$error = "{$label} must be greater than {$this->min_length}";
			if ($this->max_length !== null) {
				$error .= " and less than {$this->max_length}";
			}
			$error .= ' characters.';
			$this->addError($error);
		}
		return $min_length && $max_length;
	}
}

<?php
namespace Ali\Validator;
use Ali\Base\ValidatorAbstract;

class Required extends ValidatorAbstract {
	public $required     = true;
	public $empty_values = [null, ''];
	public function validate($object, $attribute) {
		$value  = $object->$attribute;
		if ($this->required && $this->isEmpty($value)) {
			$label = $object->getAttributeLabel($attribute);
			$this->addError("{$label} is a required field.");
			return false;
		}
		return true;
	}
	protected function isEmpty($value) {
		foreach ($this->empty_values as $empty_val) {
			if ($value === $empty_val) {
				return true;
			}
		}
		return false;
	}
}

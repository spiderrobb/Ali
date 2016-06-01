<?php
namespace Ali\Validator;
use Ali\Base\ValidatorAbstract;

class Required extends ValidatorAbstract {
	public $required = true;
	public function validate($object, $attribute) {
		$value  = $object->$attribute;
		$result = $this->required && $value !== null && $value !== '';
		if ($result === false) {
			$label = $object->getAttributeLabel($attribute);
			$this->addError("{$label} is a required field.");
		}
		return $result;
	}
}

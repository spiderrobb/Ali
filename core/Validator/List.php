<?php
namespace Ali\Validator;
class List extends Required {
	public $accept = [];
	public function validate($object, $attribute) {
		$label  = $object->getAttributeLabel($attribute);
		$value  = $object->$attribute;
		$result = true;

		if (!in_array($value, $this->accept)) {
			$this->addError("{$label} is not an acceptable value.");
			$result = false;
		}

		return $result && parent::validate($object, $attribute);
	}
}

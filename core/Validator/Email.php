<?php
namespace Ali\Validator;
class Email extends String {
	public function validate($object, $attribute) {
		$label  = $object->getAttributeLabel($attribute);
		$value  = $object->$attribute;
		$result = true;

		if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
			$this->addError("{$label} is not a valid email address.");
			$result = false;
		}

		return $result && parent::validate($object, $attribute);
	}
}

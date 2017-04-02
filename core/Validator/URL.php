<?php
namespace Ali\Validator;
class URL extends String {
	public function validate($object, $attribute) {
		$label  = $object->getAttributeLabel($attribute);
		$value  = $object->$attribute;
		$result = true;

		if (filter_var($value, FILTER_SANITIZE_URL) === false) {
			$this->addError("{$label} is not a valid url.");
			$result = false;
		}

		return $result && parent::validate($object, $attribute);
	}
}

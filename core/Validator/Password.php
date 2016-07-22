<?php
namespace Ali\Validator;
class Password extends String {
	public $confirm_attribute;
	public $trim = false;
	public function validate($object, $attribute) {
		$label             = $object->getAttributeLabel($attribute);
		$confirm_attribute = $this->confirm_attribute;
		$confirm_value     = $object->$confirm_attribute;
		$value             = $object->$attribute;
		$result            = true;

		// whitespace rule
		if (trim($value) !== $value) {
			$this->addError("{$label} cannot start or end with white space.");
			$result = false;
		}

		// matches confirmation password
		if ($value !== $confirm_value) {
			$this->addError("{$label} does not match password confirmation.");
			$result = false;
		}

		return parent::validate($object, $attribute) && $result;
	}
}

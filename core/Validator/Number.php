<?php
namespace Ali\Validator;

class Number extends Required {
	public $min      = null;
	public $max      = null;
	public $required = false;
	public function validate($object, $attribute) {
		$value = $object->$attribute;
		$label = $object->getAttributeLabel($attribute);
		if ($this->min !== null && $value < $this->min) {
			$this->addError("{$label} must not be smaller than {$this->min}.");
		}
		if ($this->max !== null && $value > $this->max) {
			$this->addError("{$label} must not be larger than ($this->max}");
		}
		return parent::validate($object, $attribute);
	}
}
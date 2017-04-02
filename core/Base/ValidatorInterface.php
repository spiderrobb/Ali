<?php
namespace Ali\Base;
interface ValidatorInterface {
	public static function getInstance(array $params);
	public function addError($message);
	public function getErrors();
	public function clearErrors();
	public function validate($object, $attribute);
	public function filter($object, $attribute);
}
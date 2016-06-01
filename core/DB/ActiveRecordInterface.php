<?php
namespace Ali\DB;

interface ActiveRecordInterface {
	public static function getInstance();
	public function getPK();
	public function findByPk($pk);
	public function find(Criteria $criteria = null);
	public function findByAttributes(array $attributes);
	public function findAll(Criteria $criteria = null);
	public function findAllByAttributes(array $attributes);
	public function getAttributeLabel($field);
	public function getAttributes();
	public function isNew();
	public function save();
	public function validate();
	public function delete();
	public function relations();
	public function addError($field, $errors);
	public function getErrors($field = null);
	// public function _beforeSave();
	// protected function _afterLoad();
	// protected function _afterSave();
	// protected function _beforeValidate();
	// protected function _validate();
	// protected function _afterValidate();
	// //protected function _validateData();
}
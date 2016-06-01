<?php
namespace Ali\DB;

trait Relation {
	// private variables
	private $_relations = array();

	// abstract functions
	abstract public function relations();
	abstract public function getPK();

	// public function for getting relations
	public function getRelation($relation) {
		if (!isset($this->_relations[$relation])) {
			$this->_relations[$relation] = $this->_getRelation($relation);
		}
		return $this->_relations[$relation];
	}

	// function for checking if a relation exists
	public function issetRelation($relation) {
		$relations = $this->relations();
		return isset($relations[$relation]);
	}

	// this function clears all relations fetched so far
	public function clearRelations() {
		$this->_relations = array();
	}

	// this function clears a specific relation
	public function clearRelation($relation) {
		if (isset($this->_relations[$relation])) {
			unset($this->_relations[$relation]);
		}
	}

	// this function gets the relation
	private function _getRelation($relation) {
		// init relation
		$relations  = $this->relations();
		$spec       = $relations[$relation];
		$spec_type  = null;
		$spec_class = null;
		$spec_key   = null;
		extract($spec, EXTR_PREFIX_ALL, 'spec');

		// generate pk -> spec key
		$pk = $this->getPK();
		if (is_array($spec_key)) {
			$attributes = array();
			foreach ($spec_key as $key => $pk_key) {
				$attributes[$key] = $pk[$pk_key];
			}
		} else {
			$attributes = array($spec_key => $pk);
		}

		// finding relation
		if ($spec_type === 'belongs_to') {
			return $spec_class::getInstance()->findByPk($this->$spec_key);
		} else if ($spec_type === 'has_many') {
			return $spec_class::getInstance()->findAllByAttributes($attributes);
		} else if ($spec_type === 'has_one') {
			return $spec_class::getInstance()->findByAttributes($attributes);
		}
		// could not find
		return null;
	}
}
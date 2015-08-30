<?php
namespace Ali\DB;
class Expression {
	private $_expression;
	private $_params;
	public function __construct($expression, array $params = array()) {
		$this->_expression = $expression;
		$this->_params     = $params;
	}
	public function getExpression() {
		return $this->_expression;
	}
	public function getParams() {
		return $this->_params;
	}
}
<?php
namespace Ali\DB;

class Criteria {
	public $select;
	public $alias;
	public $join;
	public $where;
	public $group;
	public $having;
	public $order;
	public $limit;
	public $offset;
	public $params;
	public function __construct(array $attributes = array()) {
		// initialize vars
		$this->select = '*';
		$this->alias  = 't';
		$this->join   = null;
		$this->where  = null;
		$this->group  = null;
		$this->having = null;
		$this->order  = null;
		$this->limit  = null;
		$this->offset = null;
		$this->params = array();

		// parse vars from attributes
		$vars = array(
			'select', 'alias', 'where', 
			'group', 'having', 'order', 
			'limit', 'offset', 'params'
		);
		foreach ($attributes as $var => $value) {
			if (!in_array($var, $vars)) {
				throw new Exception("var: {$var} does not exist in ".__CLASS__);
			}
			$this->$var = $value;
		}
	}
	public function getSelectSQL() {
		return 'SELECT '.$this->select;
	}
	public function getAlias() {
		return $this->alias;
	}
	public function getJoinSQL() {
		return ' '.trim($this->join);
	}
	public function getWhereSQL() {
		return $this->where !== null ? ' WHERE '.$this->where : '';
	}
	public function getGroupSQL() {
		return $this->group !== null ? ' GROUP BY '.$this->group : '';
	}
	public function getHavingSQL() {
		return $this->having !== null ? ' HAVING '.$this->having : '';
	}
	public function getOrderSQL() {
		return $this->order !== null ? ' ORDER BY '.$this->order : '';
	}
	public function getLimitSQL() {
		return $this->limit !== null ? ' LIMIT '.$this->limit : '';
	}
	public function getOffsetSQL() {
		return $this->offset !== null ? ' OFFSET '.$this->offset : '';
	}
	public function getQuery($table) {
		$sql = $this->getSelectSQL()
			." FROM {$table} ".$this->getAlias()
			.$this->getJoinSQL()
			.$this->getWhereSQL()
			.$this->getGroupSQL()
			.$this->getHavingSQL()
			.$this->getOrderSQL()
			.$this->getLimitSQL()
			.$this->getOffsetSQL();
		return $sql;
	}
}
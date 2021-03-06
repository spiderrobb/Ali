<?php
namespace Ali\Base;
abstract class TemplateAbstract implements TemplateInterface {
	use ViewLoader;
	protected $_user;
	public function __construct(UserInterface $user) {
		$this->_user = $user;
	}
	public function build($args) {
		if (isset($args['content'])) {
			echo $args['content'];
		}
	}
}
?>
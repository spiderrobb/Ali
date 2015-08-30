<?php
namespace Ali\Template;

use Ali\Base\TemplateAbstract;

class HTML extends TemplateAbstract {
	public function build($args) {
		$args['html_content'] = $this->_view('Template', $args, true);
		parent::build($args);
	}
}
?>
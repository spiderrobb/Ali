<?php
namespace Ali\Base;
interface TemplateInterface {
	public function __construct(UserInterface $user);
	public function build($args);
}
?>
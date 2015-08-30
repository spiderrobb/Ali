<!DOCTYPE html>
<html><head><title><?php
if (isset($html_title)) {
	echo $html_title;
}
?></title><?php
$package = \Ali\Package::getInstance();
$package->generateMeta();
$package->generateStyle();
$package->generateCustomStyle();
$package->generateScript();
?></head><body><?php
if (isset($html_content)) {
	echo $html_content;
}
$package->generateCustomScript();
?></body></html>
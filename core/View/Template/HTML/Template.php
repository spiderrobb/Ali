<!DOCTYPE html>
<html><head><title><?php
if (isset($title)) {
	echo $title;
}
?></title><?php
$package = \Ali\Package::getInstance();
$package->generateMeta();
$package->generateStyle();
$package->generateCustomStyle();
$package->generateScript();
?></head><body><?php
if (isset($content)) {
	echo $content;
}
$package->generateCustomScript();
?></body></html>
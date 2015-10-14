<?php
use Ali\Package;
$package  = Package::getInstance();
$response = array(
	'scripts' => $package->getScriptLinks(),
	'styles'  => $package->getStyleLinks(),
	'js'      => $package->getScriptCalls(),
	'css'     => $package->generateCustomStyle()
);
if (isset($title)) {
	$response['title'] = $title;
}
if (isset($content)) {
	$response['html'] = $content;
}
if (isset($data)) {
	$response['data'] = $data;
}
echo json_encode($response);
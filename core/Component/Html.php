<?php
namespace Ali\Component;

class Html {
	public static function tagParams(array $htmloptions) {
		$param_string = '';
		foreach ($htmloptions as $key => $value) {
			$value         = str_replace('"', '&quot;', $value);
			$param_string .= " {$key}=\"{$value}\"";
		}
		return $param_string;
	}
	public static function startTag($name, array $htmloptions = array(), $singleton = false) {
		return '<'.$name.self::tagParams($htmloptions).($singleton ? '/' : '').'>';
	}
	public static function endTag($name) {
		return '</'.$name.'>';
	}
	public static function tag($name, $value, array $htmloptions = array(), $sanitize = true) {
		return self::startTag($name, $htmloptions)
			.($sanitize ? htmlentities($value) : $value)
			.self::endTag($name);
	}
	public static function startForm($url, $method, array $htmloptions = array()) {
		$htmloptions['action'] = $action;
		$htmloptions['method'] = strtoupper($method);
		return self::startTag('form', $htmloptions);
	}
	public static function endForm() {
		return self::endTag('form');
	}
	public static function input($type, $name, $value, array $htmloptions = array()) {
		if ($type !== null) {
			$htmloptions['type']  = $type;
		}
		if ($name !== null) {
			$htmloptions['name']  = $name;
		}
		if ($value !== null) {
			$htmloptions['value'] = $value;
		}
		return self::startTag('input', $htmloptions, true);
	}
	public static function inputText($name, $value, array $htmloptions = array()) {
		return self::input('text', $name, $value, $htmloptions);
	}
	public static function inputTextArea($name, $value, array $htmloptions = array()) {
		if ($name !== null) {
			$htmloptions['name'] = $name;
		}
		return self::tag('textarea', $value, $htmloptions);	
	}
	public static function inputEmail($name, $value, array $htmloptions = array()) {
		return self::input('email', $name, $value, $htmloptions);
	}
	public static function inputHidden($name, $value, array $htmloptions = array()) {
		return self::input('hidden', $name, $value, $htmloptions);
	}
	public static function inputPassword($name, $value, array $htmloptions = array()) {
		return self::input('password', $name, $value, $htmloptions);
	}
	public static function inputSelect($name, $value, $options, array $htmloptions = array()) {
		$values = '';
		foreach ($options as $key => $label) {
			$params  = array('value' => $key);
			if ($value === $key) {
				$params['selected'] = "selected";
			}
			$values .= self::tag('option', $label, $params);
		}
		if ($name !== null) {
			$htmloptions['name'] = $name;
		}
		return self::tag('select', $values, $htmloptions, false);
	}
	public static function inputLabel($label, array $htmloptions = array()) {
		return self::tag('label', $label, $htmloptions);
	}
	public static function button($label, array $htmloptions = array()) {
		return self::tag('button', $label, $htmloptions, false);
	}
	public static function inputSubmit($name, $value, array $htmloptions = array()) {
		return self::input('submit', $name, $value, $htmloptions);
	}
}

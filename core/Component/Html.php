<?php
namespace Ali\Component;

use Ali\DB\ActiveRecord;
use ReflectionClass;

class Html {
	// start tag helpers
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
		if ($sanitize) {
			if (mb_detect_encoding($value, 'UTF-8', true)) {
				$value = utf8_decode($value);
			}
			$value = preg_replace_callback(
				"/(&#[0-9]+;)/", function($m) {
					return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); 
				}, $value
			); 
			$value = htmlspecialchars($value);
		}
		return self::startTag($name, $htmloptions).$value.self::endTag($name);
	}

	// start form helpers
	public static function startForm($action, $method, array $htmloptions = array()) {
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
	public static function inputUrl($name, $value, array $htmloptions = array()) {
		return self::input('url', $name, $value, $htmloptions);
	}
	public static function inputHidden($name, $value, array $htmloptions = array()) {
		return self::input('hidden', $name, $value, $htmloptions);
	}
	public static function inputPassword($name, $value, array $htmloptions = array()) {
		return self::input('password', $name, $value, $htmloptions);
	}
	public static function inputSelect($name, $value, array $options, array $htmloptions = array()) {
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
	public static function label($label, array $htmloptions = array()) {
		return self::tag('label', $label, $htmloptions);
	}
	public static function button($label, array $htmloptions = array()) {
		return self::tag('button', $label, $htmloptions, false);
	}
	public static function inputSubmit($name, $value, array $htmloptions = array()) {
		return self::input('submit', $name, $value, $htmloptions);
	}

	// start active form helpers
	public static function modelName(ActiveRecord $record, $attribute) {
		$ref = new ReflectionClass($record);
		return $ref->getShortName().'['.$attribute.']';
	}
	public static function activeInputText(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputText($name, $value, $htmloptions);
	}
	public static function activeInputTextArea(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputTextArea($name, $value, $htmloptions);
	}
	public static function activeInputEmail(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputEmail($name, $value, $htmloptions);
	}
	public static function activeInputUrl(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputUrl($name, $value, $htmloptions);
	}
	public static function activeInputHidden(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputHidden($name, $value, $htmloptions);
	}
	public static function activeInputPassword(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputPassword($name, $value, $htmloptions);
	}
	public static function activeInputSelect(ActiveRecord $record, $attribute, array $options, array $htmloptions = array()) {
		$name  = self::modelName($record, $attribute);
		$value = $record->$attribute;
		return self::inputSelect($name, $value, $options, $htmloptions);
	}
	public static function activeLabel(ActiveRecord $record, $attribute, array $htmloptions = array()) {
		$label = $record->getAttributeLabel($attribute);
		return self::label($label, $htmloptions);
	}
}

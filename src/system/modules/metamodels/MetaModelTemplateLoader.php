<?php

abstract class MetaModelTemplateLoader {

	public static function getTemplateFile($name, $format) {
		$major = version_compare(VERSION, '3.0', '>=') ? 3 : 2;
		$method = "getContao${major}TemplateFile";
		$file = self::$method($name, $format);
		if($file === null) {
			throw new Exception(sprintf('Could not find template "%s" with format "%s"', $name, $format));
		}
		return $file;
	}

	public static function getTemplateGroup() {
		TL_MODE == 'FE' && $group = trim(str_replace('../', '', $GLOBALS['objPage']->templateGroup), '/');
		strlen($group) || $group = 'templates';
		return $group;
	}

	protected static function getContao3TemplateFile($name, $format) {
		return \TemplateLoader::getPath($name, $format, self::getTemplateGroup());
	}

	protected static function getContao2TemplateFile($name, $format) {
		$filename = "$name.$format";
		$group = self::getTemplateGroup(true);

		$file = self::findInCache($filename, $group);
		if($file !== null) {
			return $file;
		}

		$file = self::findInTemplates($filename, $group, $keys);
		$file === null && $file = self::findInModules($filename);

		self::updateCache($keys, $file);
		return $file;
	}

	protected static function findInCache($filename, $group) {
		if($GLOBALS['TL_CONFIG']['debugMode']) {
			return;
		}
		$cache = FileCache::getInstance('templates');
		$key = $group . '/' . $filename;
		if(isset($cache->$key)) {
			$file = TL_ROOT . '/' . $cache->$key;
			if(is_file($file)) {
				return $file;
			} else {
				unset($cache->$key);
			}
		}
	}

	protected static function findInTemplates($filename, $group, &$keys = null) {
		$keys = array();
		do {
			$keys[] = $key = $group . '/' . $filename;
			$file = TL_ROOT . '/' . $key;
			if(is_file($file)) {
				return $file;
			}
			$group = dirname($group);
		} while($group != '.');
	}

	protected static function findInModules($filename) {
		$modules = Config::getInstance()->getActiveModules();
		for($i = count($modules) - 1; $i >= 0; $i--) {
			$file = TL_ROOT . '/system/modules/' . $modules[$i] . '/templates/' . $filename;
			if(is_file($file)) {
				return $file;
			}
		}
	}

	protected static function updateCache(array $keys, $file) {
		$cache = FileCache::getInstance('templates');
		if($file === null) {
			foreach($keys as $key) {
				unset($cache->$key);
			}
		} else {
			foreach($keys as $key) {
				$cache->$key = $file;
			}
		}
	}

	private final function __construct() {
	}

	private final function __clone() {
	}

}

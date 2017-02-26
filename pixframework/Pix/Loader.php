<?php

/**
 * Pix_Loader 處理 PHP AutoLoad Class 的功能
 * 
 * @package Loader
 * @copyright 2003-2012 PIXNET Digital Media Corporation
 * @license http://framework.pixnet.net/license BSD License
 */
class Pix_Loader
{
    protected static function autoload($class)
    {
	if (class_exists($class, false) or interface_exists($class, false)) {
	    return false;
	}

	$class = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

	$paths = explode(PATH_SEPARATOR, get_include_path());
	foreach ($paths as $path) {
	    $path = rtrim($path, '/');
	    if (file_exists($path . '/' . $class)) {
		require $class;

		return true;
	    }
	}

	return false;
    }

    protected static function autoloadNamespace($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	$paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            $path = rtrim($path, '/');
            $file_path = $path . '/' . $fileName;
	    if (file_exists($file_path)) {
		require $file_path;
            }
        }
    }

    /**
     * registerAutoload 呼叫這 function 就會讓 PHP 在 Load Foo_Bar 的 class 時，會去 include path 找 Foo/Bar.php
     * 
     * @static
     * @access public
     * @return void
     */
    public static function registerAutoload()
    {
	spl_autoload_register(array('Pix_Loader', 'autoload'));
	spl_autoload_register(array('Pix_Loader', 'autoloadNamespace'));
    }
}

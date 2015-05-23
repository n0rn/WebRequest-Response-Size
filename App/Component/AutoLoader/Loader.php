<?php

namespace App\Component\AutoLoader;

/**
 * Autoloader class
 * Class to manage autoload classes
 * 
 * @author vignesh
 */
class Loader
{
	private $classMap = array();
	
	private $basePath;
	
	/**
	 * Initializes the AutoLoader Class
	 * 
	 * @param string $basePath
	 */
	public function __construct($basePath)
	{
        $this->basePath = $basePath;	    
	}
	
	/**
	 * Registers this instance as an autoloader.
	 */
	public function register()
	{
	    spl_autoload_register(array($this, 'loadClass'), true);
	}
	
	/**
	 * Unregisters this instance as an autoloader.
	 */
	public function unregister()
	{
	    spl_autoload_unregister(array($this, 'loadClass'));
	}
	
	/**
	 * Loads the given class or interface.
	 *
	 * @param  string    $class The name of the class
	 * @return bool|null True if loaded, null otherwise
	 */
	public function loadClass($class)
	{
	    if ($file = $this->findFile($class)) {
	        require_once $file;
	
	        return true;
	    }
	}
	
	/**
	 * Finds the path to the file where the class is defined.
	 *
	 * @param string $class The name of the class
	 *
	 * @return string|false The path if found, false otherwise
	 */
	public function findFile($class)
	{
	    $file = NULL;
	    
	    // class map lookup
	    if (isset($this->classMap[$class])) {
	        return $this->classMap[$class];
	    }

	    $fileLocationInClass = str_replace('\\', '/', $class);
        
        $classLocation = $this->basePath.'/'.$fileLocationInClass.'.php';

        if(file_exists($classLocation)) {
        	$file = $classLocation;
        }
	
	    if ($file === null) {
	        // Remember that this class does not exist.
	        return $this->classMap[$class] = false;
	    }
	
	    return $file;
	}
}
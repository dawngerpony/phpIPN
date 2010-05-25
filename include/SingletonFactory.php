<?php
/**
 *
 */
class SingletonFactory
{
	protected $singletons;       // array of singleton objects
    protected static $singleton; // singleton of SingletonFactory

    /**
     * @return instance of an object
     */
	function getSingleton($class, $params = array()) {
	    if(!isset($this->singletons[$class])) {
	        $this->singletons[$class] = new $class($params);
	    }
	    return $this->singletons[$class];
	}
	
    /**
     * @return instance of an object
     */
	function setSingleton($class, $object) {
        $this->singletons[$class] = $object;
	}

    /**
     * @return instance of an object
     */
	function resetSingletons() {
        unset($this->singletons);
	}

	/**
	 * @return instance of the SingletonFactory object
	 */
    static function getInstance() {
        if(!isset(self::$singleton)) {
            self::$singleton = new SingletonFactory();
        }
        return self::$singleton;
    }
}

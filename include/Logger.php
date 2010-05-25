<?php
/**
 * Logger - manages the logging
 */
class Logger
{
    private static $logInstance = null;

    private static function init()
    {
        $filename = Config::$logFile;
        self::$logInstance = &Log::singleton('file', $filename);
    }

    public static function notice($msg)
    {
        if(null === self::$logInstance)
        {
            self::init();
        }
        self::$logInstance->notice($msg);
    }

    /**
     * Alias for warning().
     */
    public static function warn($msg)
    {
        self::warning($msg);
    }
    
    public static function warning($msg)
    {
        if(null === self::$logInstance)
        {
            self::init();
        }
        self::$logInstance->warning($msg);
    }
    
    public static function debug($msg)
    {
        if(null === self::$logInstance)
        {
            self::init();
        }
        self::$logInstance->debug($msg);
    }

    /**
     * Alias for err().
     */
    public static function error($msg)
    {
        self::err($msg);
    }

    public static function err($msg)
    {
        if(null === self::$logInstance)
        {
            self::init();
        }
        self::$logInstance->err($msg);
    }

    public static function info($msg)
    {
        if(null === self::$logInstance)
        {
            self::init();
        }
        self::$logInstance->info($msg);
    }

}


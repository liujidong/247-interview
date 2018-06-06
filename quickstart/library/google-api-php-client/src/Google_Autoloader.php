<?php

/**
 * Google Client API Autoloader.
 */
class Google_Autoloader
{

    private $baseDir;

    /**
     * Autoloader constructor.
     *
     * @param string $baseDir Google Client API library base directory (default: dirname(__FILE__))
     */
    public function __construct($baseDir = null)
    {
        if ($baseDir === null) {
            $this->baseDir = dirname(__FILE__);
        } else {
            $this->baseDir = rtrim($baseDir, '/');
        }
    }

    /**
     * Register a new instance as an SPL autoloader.
     *
     * @param string $baseDir Google Client API library base directory (default: dirname(__FILE__))
     *
     * @return Google_Autoloader Registered Autoloader instance
     */
    public static function register($baseDir = null)
    {
        $loader = new self($baseDir);
        spl_autoload_register(array($loader, 'autoload'));

        return $loader;
    }

    /**
     * Autoload Google classes.
     *
     * @param string $class
     */
    public function autoload($class)
    {
        if(strncmp($class, 'Google_',7)){
            return;
        }
        require_once("Google_Client.php");

        // autoload class under contrib
        $contrib_dir = $this->baseDir . '/contrib/';
        $file_path = "$contrib_dir$class.php";
        if(file_exists($file_path)) {
            require_once("$file_path");
        }
    }
}

<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitd4973892c854bfb2f3b8852804f7fc10
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitd4973892c854bfb2f3b8852804f7fc10', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitd4973892c854bfb2f3b8852804f7fc10', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitd4973892c854bfb2f3b8852804f7fc10::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

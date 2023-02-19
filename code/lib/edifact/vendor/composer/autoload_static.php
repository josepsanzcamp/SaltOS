<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit95a1541958b605ba1857b9a0c384ba7a
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '65380ab4d81167209b0a4fac46641984' => __DIR__ . '/..' . '/voku/arrayy/src/Create.php',
    );

    public static $prefixLengthsPsr4 = array (
        'p' => 
        array (
            'phpDocumentor\\Reflection\\' => 25,
        ),
        'W' => 
        array (
            'Webmozart\\Assert\\' => 17,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
        ),
        'E' => 
        array (
            'EDI\\' => 4,
        ),
        'A' => 
        array (
            'Arrayy\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'phpDocumentor\\Reflection\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpdocumentor/reflection-common/src',
            1 => __DIR__ . '/..' . '/phpdocumentor/reflection-docblock/src',
            2 => __DIR__ . '/..' . '/phpdocumentor/type-resolver/src',
        ),
        'Webmozart\\Assert\\' => 
        array (
            0 => __DIR__ . '/..' . '/webmozart/assert/src',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'EDI\\' => 
        array (
            0 => __DIR__ . '/..' . '/sabas/edifact/src/EDI',
        ),
        'Arrayy\\' => 
        array (
            0 => __DIR__ . '/..' . '/voku/arrayy/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit95a1541958b605ba1857b9a0c384ba7a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit95a1541958b605ba1857b9a0c384ba7a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit95a1541958b605ba1857b9a0c384ba7a::$classMap;

        }, null, ClassLoader::class);
    }
}

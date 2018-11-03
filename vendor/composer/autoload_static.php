<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit12e5ec45cece8b10a6007d0309150d35
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit12e5ec45cece8b10a6007d0309150d35::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit12e5ec45cece8b10a6007d0309150d35::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

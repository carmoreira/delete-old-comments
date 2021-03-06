<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit88bd39167beff480deae7a7cbe0354b3
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Arte\\WP\\Plugin\\DeleteComments\\Tests\\' => 36,
            'Arte\\WP\\Plugin\\DeleteComments\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Arte\\WP\\Plugin\\DeleteComments\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'Arte\\WP\\Plugin\\DeleteComments\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit88bd39167beff480deae7a7cbe0354b3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit88bd39167beff480deae7a7cbe0354b3::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

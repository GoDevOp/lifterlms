<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit25a1be6c5b5334fe99a8e87e93b8ef0c
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LLMS\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LLMS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit25a1be6c5b5334fe99a8e87e93b8ef0c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit25a1be6c5b5334fe99a8e87e93b8ef0c::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
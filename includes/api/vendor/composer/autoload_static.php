<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf9563293f75a9af36e7d5506e4cdf576
{
    public static $files = array (
        '5d9c5be1aa1fbc12016e2c5bd16bbc70' => __DIR__ . '/..' . '/dusank/knapsack/src/collection_functions.php',
        'e5fde315a98ded36f9b25eb160f6c9fc' => __DIR__ . '/..' . '/dusank/knapsack/src/utility_functions.php',
        'a9ed0d27b5a698798a89181429f162c5' => __DIR__ . '/..' . '/khanamiryan/qrcode-detector-decoder/lib/Common/customFunctions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'Z' =>
        array (
            'Zxing\\' => 6,
        ),
        'T' =>
        array (
            'Test\\' => 5,
        ),
        'S' => 
        array (
            'Simplon\\Mysql\\' => 14,
            'Simplon\\Helper\\' => 15,
        ),
        'D' => 
        array (
            'DusanKasan\\Knapsack\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zxing\\' =>
        array (
            0 => __DIR__ . '/..' . '/khanamiryan/qrcode-detector-decoder/lib',
        ),
        'Test\\' =>
        array (
            0 => __DIR__ . '/..' . '/simplon/mysql/test',
        ),
        'Simplon\\Mysql\\' => 
        array (
            0 => __DIR__ . '/..' . '/simplon/mysql/src',
        ),
        'Simplon\\Helper\\' => 
        array (
            0 => __DIR__ . '/..' . '/simplon/helper/src',
        ),
        'DusanKasan\\Knapsack\\' => 
        array (
            0 => __DIR__ . '/..' . '/dusank/knapsack/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'W' =>
        array (
            'Webpatser\\Uuid' =>
            array (
                0 => __DIR__ . '/..' . '/webpatser/laravel-uuid/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf9563293f75a9af36e7d5506e4cdf576::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf9563293f75a9af36e7d5506e4cdf576::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitf9563293f75a9af36e7d5506e4cdf576::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}

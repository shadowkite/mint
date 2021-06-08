<?php

namespace Mint;

class Db {
    public static $em;

    public static function getEM() {
        if(self::$em) {
            return self::$em;
        }
        $paths = array(__DIR__ . "/../../src/Mint/Models");
        $isDevMode = false;

        // the connection configuration
        $dbParams = array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__ . '/../../mint.sqlite'
        );
        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        self::$em = \Doctrine\ORM\EntityManager::create($dbParams, $config);
        return self::$em;
    }
}
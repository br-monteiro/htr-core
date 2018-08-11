<?php
namespace HTR\Database;

use App\System\Configuration as cfg;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

class EntityAbstract
{

    private static $entityManager;
    private static $adjustmentsToPath = __DIR__ . '/../../../';

    /**
     * Config the Entity Manager of Doctrine
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     */
    private static function configEntityManager()
    {
        if (self::$entityManager) {
            return;
        }

        $isDevMode = isDevMode();
        $paths = [self::$adjustmentsToPath . cfg::PATH_ENTITIES];

        // the connection configuration
        if ($isDevMode) {
            $dbParams = cfg::DATABASE_CONFIGS_DEV;
        } else {
            $dbParams = cfg::DATABASE_CONFIGS_PRD;
        }

        $cache = new ArrayCache();
        $reader = new AnnotationReader();
        $driver = new AnnotationDriver($reader, $paths);

        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setMetadataDriverImpl($driver);

        self::$entityManager = EntityManager::create($dbParams, $config);
    }

    /**
     * Return an instance of \Doctrine\ORM\EntityManager
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return EntityManager
     */
    public static function em(): EntityManager
    {
        self::configEntityManager();
        return self::$entityManager;
    }
}

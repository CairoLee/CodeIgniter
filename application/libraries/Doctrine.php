<?php
/**
 * Part of CodeIgniter Doctrine
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/codeigniter-doctrine
 */

/*
 * This code is based on http://doctrine-orm.readthedocs.org/en/latest/cookbook/integrating-with-codeigniter.html
 */

require_once APPPATH . '../vendor/autoload.php';

use Doctrine\Common\ClassLoader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\Tools\Setup;

class Doctrine
{
    public $em = null;

    public function __construct()
    {
        // load database configuration from CodeIgniter
        if (! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/database.php')
            && ! file_exists($file_path = APPPATH.'config/database.php')) {
            throw new Exception('The configuration file database.php does not exist.');
        }
        require $file_path;

        // Database connection information
        $connectionOptions = $this->convertDbConfig($db['default']);

        // With this configuration, your model files need to be in application/models/Entity
        // e.g. Creating a new Entity\User loads the class from application/models/Entity/User.php
        $models_namespace = 'Entities';
        $models_path = APPPATH . 'models';
        $proxies_dir = APPPATH . 'models/Proxies';
        $metadata_paths = array(APPPATH . 'models/Entities');

        // Set $dev_mode to TRUE to disable caching while you develop
        $dev_mode = (ENVIRONMENT !== 'production');

        // --------------------------------------------------------------------
        // 设置 Redis 缓存
        // --------------------------------------------------------------------
        // 虽然在 Doctrine 内部已经实现了缓存逻辑，但是第一优先级并不是使用 Redis
        // 所以这里我们单独设置一下，优先使用 Redis 缓存
        //
        // Doctrine 内部关于缓存的逻辑详见：
        // vendor/doctrine/orm/lib/Doctrine/ORM/Tools/Setup.php 第 126 行
        //
        // 如果 $cache 为 null 且不是开发模式的话，就会依次使用：apc、xcache、memcache、redis 缓存
        // 如果是在开发模式下的话，则使用 ArrayCache
        // --------------------------------------------------------------------
        $cache = NULL;
        if ( ! $dev_mode)
        {
            if (extension_loaded('redis'))
            {
                // 如果在生产环境，且 redis 模块已被 php 加载，那么使用 redis 作为缓存驱动器
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                $cache = new \Doctrine\Common\Cache\RedisCache();
                $cache->setRedis($redis);
            }
            else
            {
                // 若 redis 模块没有被加载的话，也使用 ArrayCache 作为缓存驱动器
                $cache = new ArrayCache();
            }
        }
        else
        {
            // 如果是在开发模式下的话，则使用 ArrayCache 作为缓存驱动器
            $cache = new ArrayCache();
        }

        // If you want to use a different metadata driver, change createAnnotationMetadataConfiguration
        // to createXMLMetadataConfiguration or createYAMLMetadataConfiguration.
        $config = Setup::createAnnotationMetadataConfiguration($metadata_paths, $dev_mode, $proxies_dir, $cache);

        // --------------------------------------------------------------------
        // Set up logger
        // 若取消注释下面两行代码的话，在执行 vendor/bin/doctrine 的时候
        // 会将相关的查询语句全部输出到终端（便于调试排错）
        // --------------------------------------------------------------------
        // $logger = new EchoSQLLogger;
        // $config->setSQLLogger($logger);

        $this->em = EntityManager::create($connectionOptions, $config);

        $loader = new ClassLoader($models_namespace, $models_path);
        $loader->register();
    }

    /**
     * Convert CodeIgniter database config array to Doctrine's
     *
     * See http://www.codeigniter.com/user_guide/database/configuration.html
     * See http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
     *
     * @param array $db
     * @return array
     * @throws Exception
     */
    public function convertDbConfig($db)
    {
        $connectionOptions = [];

        if ($db['dbdriver'] === 'pdo') {
            return $this->convertDbConfigPdo($db);
        } elseif ($db['dbdriver'] === 'mysqli') {
            $connectionOptions = [
                'driver'   => $db['dbdriver'],
                'user'     => $db['username'],
                'password' => $db['password'],
                'host'     => $db['hostname'],
                'dbname'   => $db['database'],
                'charset'  => $db['char_set'],
            ];
        } else {
            throw new Exception('Your Database Configuration is not confirmed by CodeIgniter Doctrine');
        }

        return $connectionOptions;
    }

    protected function convertDbConfigPdo($db)
    {
        $connectionOptions = [];

        if (substr($db['hostname'], 0, 7) === 'sqlite:') {
            $connectionOptions = [
                'driver'   => 'pdo_sqlite',
                'user'     => $db['username'],
                'password' => $db['password'],
                'path'     => preg_replace('/\Asqlite:/', '', $db['hostname']),
            ];
        } elseif (substr($db['dsn'], 0, 7) === 'sqlite:') {
            $connectionOptions = [
                'driver'   => 'pdo_sqlite',
                'user'     => $db['username'],
                'password' => $db['password'],
                'path'     => preg_replace('/\Asqlite:/', '', $db['dsn']),
            ];
        } elseif (substr($db['dsn'], 0, 6) === 'mysql:') {
            $connectionOptions = [
                'driver'   => 'pdo_mysql',
                'user'     => $db['username'],
                'password' => $db['password'],
                'host'     => $db['hostname'],
                'dbname'   => $db['database'],
                'charset'  => $db['char_set'],
            ];
        } else {
            throw new Exception('Your Database Configuration is not confirmed by CodeIgniter Doctrine');
        }

        return $connectionOptions;
    }
}

<?php

namespace Tiny\Plugin;

use Illuminate\Database\Capsule\Manager;
use Tiny\Application;

class DbHelper extends Manager
{
    /**
     * @return Manager
     */
    public static function initDb()
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }
        $db_config = self::getBaseConfig();
        $db = new DbHelper();
        $db->addConnection($db_config, $db_config['database']);
        $db->setAsGlobal();
        return self::$instance;
    }

    private static function getBaseConfig()
    {
        $app = Application::app();
        $db_config = [
            'driver' => 'mysql',
            'host' => $app->getEnv('ENV_MYSQL_HOST'),
            'port' => $app->getEnv('ENV_MYSQL_PORT'),
            'database' => strtolower($app->getEnv('ENV_MYSQL_DB')),
            'username' => $app->getEnv('ENV_MYSQL_USER'),
            'password' => $app->getEnv('ENV_MYSQL_PASS'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];
        return $db_config;
    }

    /**
     * @param string|array $config
     * @return \Illuminate\Database\Connection
     */
    public function getConnection($config = null)
    {
        if ( is_string($config) ) {
            $db_config = self::getBaseConfig();
            $db_config['database'] = strtolower($config);
            $key = $db_config['database'];
        } else if ( is_array($config) ){
            $db_config = $config;
            $key = "{$db_config['host']}:{$db_config['port']}@{$db_config['username']}#{$db_config['database']}";
        } else {
            $db_config = $config;
            $key = md5(print_r($config, true));
        }
        parent::addConnection($db_config, $key);
        return $this->manager->connection($key);
    }

}



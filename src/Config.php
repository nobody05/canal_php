<?php


namespace PhpOne\CanalPHP;


class Config
{
    const CONFIG_FILE_EXT = '.php';

    private static $configFileName;
    private static $configEnvFilePath;
    private static $configGlobalFilePath;
    private static $configItemName;

    /**
     * @param string $configName
     * @return array|null|string|int
     */
    public static function get(string $configName = 'Global')
    {
        $configName = trim($configName, '.');
        if (empty($configName)) {
            return null;
        }

        $configName = explode('.', $configName, 2);
        self::$configFileName = empty($configName[0]) ? null : $configName[0];
        self::$configItemName = empty($configName[1]) ? null : $configName[1];

        return self::getConfig();
    }

    private static function getConfig()
    {
        self::$configGlobalFilePath = CANAL_CONFIG_DIR . DIRECTORY_SEPARATOR . self::$configFileName . self::CONFIG_FILE_EXT;
        self::$configEnvFilePath = CANAL_CONFIG_DIR . DIRECTORY_SEPARATOR . self::$configFileName
            . self::CONFIG_FILE_EXT;

        print_r(self::$configGlobalFilePath);

        if (! is_file(self::$configGlobalFilePath) && ! is_file(self::$configEnvFilePath)) {
            return null;
        }

        if (empty(self::$configItemName)) {
            $config = self::getAllConfigItems();
        } else {
            $config = self::getConfigItem();
        }

        return $config;
    }

    private static function getAllConfigItems()
    {
        $globalConfig = $envConfig = [];
        if (is_file(self::$configGlobalFilePath)) {
            $globalConfig = require self::$configGlobalFilePath;
        }
        if (is_file(self::$configEnvFilePath)) {
            $envConfig = require self::$configEnvFilePath;
        }

        return array_merge($globalConfig, $envConfig);
    }

    private static function getConfigItem()
    {
        if (empty(self::$configItemName)) {
            return null;
        }

        $allConfig = self::getAllConfigItems();
        $keys = explode('.', self::$configItemName);
        $tmpConfig = $allConfig;
        foreach ($keys as $key) {
            if (empty($tmpConfig[$key])) {
                $tmpConfig = null;
                break;
            }
            $tmpConfig = $tmpConfig[$key];
        }

        return $tmpConfig;
    }

    private function __construct()
    {
    }
}
<?php

namespace backtend\phpfifth;


class Environ
{

    const ENVIRON_DEV = 'dev';//开发环境
    const ENVIRON_BOX = 'box';//沙盒环境（后台有按钮随时推倒数据库非必要数据数据重来）
    const ENVIRON_TEST = 'test';//测试环境
    const ENVIRON_PRE = 'pre';//预发布环境（用生产环境的数据库，用预发布的最新程序版本）
    const ENVIRON_PROD = 'prod';//生产环境

    const SESSION_KEY = 'env_session_';//会话测试的key

    private static $_data = array();
    private static $_env = null;


    /**
     * 私有拒绝构造
     */
    private function __construct()
    {
    }

    /**
     * 私有拒绝克隆
     */
    private function __clone()
    {
    }


    /**
     * 初始化
     * @throws \Exception
     */
    protected static function init()
    {
        if (self::$_env !== null) {
            return;
        }

        $rootPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;//halts($rootPath);
        //$rootPath = 'D:\backtend\testing/';//开发中的是快捷方式软连接，临时启用绝对路径
        // 加载环境变量
        $envFile = $rootPath . '.env';

        if (is_file($envFile)) {
            //$env = parse_ini_file($envFile, true) ?: [];
            $iniString = file_get_contents($envFile) . "\r";
            $iniSeeds = array('null' => null, 'empty' => '', 'true' => true, 'false' => false);
            $prefixReplace = sprintf('tmp_environ_%s_', uniqid());
            foreach ($iniSeeds as $k => $v) {
                //$iniString = preg_replace("/=\s?\($k\)\r/i", "=$v\r", $iniString);
                $iniString = preg_replace("/=\s*\(?$k\)?\r/i", "=$prefixReplace$k\r", $iniString);
            }
            $env = parse_ini_string($iniString, true) ?: array();
            $seedTmp = array();
            foreach ($iniSeeds as $k => $v) {
                $seedTmp[$prefixReplace . $k] = $v;
            }
            $env = array_change_key_case($env, CASE_UPPER);
            $env = array_map(function ($v1) use ($seedTmp) {
                if (is_array($v1)) {
                    return array_map(function ($v2) use ($seedTmp) {
                        return (current(array_keys($seedTmp)) === $v2 or isset($seedTmp[$v2])) ? $seedTmp[$v2] : $v2;
                    }, $v1);
                }
                return (current(array_keys($seedTmp)) === $v1 or isset($seedTmp[$v1])) ? $seedTmp[$v1] : $v1;
            }, $env);
            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        self::$_data[$key . '_' . strtoupper($k)] = $v;
                    }
                } else {
                    self::$_data[$key] = $val;
                }
            }

            $envTags = array('APP_ENV', 'APP_ENVIRON');//若存在则以最后的为准
            foreach ($envTags as $envTag) {
                if (isset(self::$_data[$envTag]) and self::$_data[$envTag]) {
                    self::$_env = self::$_data[$envTag];
                }
            }
        }

        self::$_env = self::$_env ?: 'dev';//无.env文件或配置默认为dev环境

        if (!in_array(self::$_env, self::all())) {
            throw new \Exception('environ must in:' . implode(',', self::all()));
        }
    }


    /**
     * 获取env配置数据
     * @param string|null $name
     * @param null $default
     * @return array|bool|false|mixed|string|null
     * @throws \Exception
     */
    public static function get($name = null, $default = null)
    {
        self::init();//initial

        if (is_null($name)) {
            return self::$_data;
        }

        $name = strtoupper(str_replace('.', '_', $name));

        if (isset(self::$_data[$name])) {
            return self::$_data[$name];
        }

        $result = getenv('PHP_' . $name);

        if (false === $result) {
            return $default;
        }

        if ('false' === $result) {
            $result = false;
        } elseif ('true' === $result) {
            $result = true;
        }

        if (!isset(self::$_data[$name])) {
            self::$_data[$name] = $result;
        }

        return $result;
    }


    /**
     * 获取当前环境
     * @return string|null
     * @throws \Exception
     */
    public static function tag()
    {
        self::init();//initial

        return self::$_env;
    }

    /**
     * 获取所有环境
     * @return array
     */
    public static function all()
    {
        return array(self::ENVIRON_DEV, self::ENVIRON_BOX, self::ENVIRON_TEST, self::ENVIRON_PRE, self::ENVIRON_PROD);
    }


    /**
     * 线上环境（预发布环境or生产环境）
     */
    public static function isOnline()
    {
        return self::isPre() or self::isProd();
    }

    /**
     * 线下环境（非线上环境）
     */
    public static function isOffline()
    {
        return !self::isOnline();
    }


    /**
     * 是其中一个环境
     */
    public static function isEnv()
    {
        //Environ::isEnv('test')
        //Environ::isEnv('dev','test')
        $args = func_get_args();
        if (in_array(self::tag(), $args))
            return true;
        return false;
    }

    public static function notEnv()
    {
        //Environ::notEnv('test')
        //Environ::notEnv('dev','test')
        $args = func_get_args();
        if (!in_array(self::tag(), $args))
            return true;
        return false;
    }


    /**
     * 开发环境
     */
    public static function isDev()
    {
        return self::tag() === self::ENVIRON_DEV;
    }

    public static function notDev()
    {
        return !self::isDev();
    }

    /**
     * 沙盒环境
     */
    public static function isBox()
    {
        return self::tag() === self::ENVIRON_BOX;
    }

    public static function notBox()
    {
        return !self::isBox();
    }

    /**
     * 测试环境
     */
    public static function isTest()
    {
        return self::tag() === self::ENVIRON_TEST;
    }

    public static function notTest()
    {
        return !self::isTest();
    }


    /**
     * 预发布环境
     */
    public static function isPre()
    {
        return self::tag() === self::ENVIRON_PRE;
    }

    public static function notPre()
    {
        return !self::isPre();
    }


    /**
     * 生产环境
     */
    public static function isProd()
    {
        return self::tag() === self::ENVIRON_PROD;
    }

    public static function notProd()
    {
        return !self::isProd();
    }


}
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple Redis wrapper module for Kohana 3.2+ (https://github.com/nicolasff/phpredis)
 *
 * @author  2BJ (gmail@dev2bj)
 * @TODO:
 *      - add multi servers
 */
class Kredis extends Redis {

    const DEFAULT_HOSTNAME = '127.0.0.1';
    const DEFAULT_PORT     = 6379;
    const DEFAULT_TIMEOUT  = 0; // 0 - unlimited
    const DEFAULT_DATABASE = 0;

    public static $default = 'default';

    public static $instances = [];

    /**
     * @param null $name
     * @param array $config
     * @return Redis
     */
    public static function instance($name = NULL, array $config = NULL)
    {
        if ($name === NULL)
        {
            $name = self::$default;
        }

        if ( ! isset(self::$instances[$name]))
        {
            if ($config === NULL)
            {
                $config = Kohana::$config->load('kredis')->$name;
            }

            new self($name, $config);
        }

        return self::$instances[$name];
    }

    protected $_instance;

    protected $_config;

    /**
     * @param $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->_instance = $name;

        $this->_config = $config;

        $hostname = self::DEFAULT_HOSTNAME;
        $port     = self::DEFAULT_PORT;
        $timeout  = self::DEFAULT_TIMEOUT;
        $database = self::DEFAULT_DATABASE;
        $connect  = 'connect';

        if (isset($config['connection']))
        {
            if (is_string($config['connection']))
            {
                $connect_with_socket = $config['connection'];
            }
            elseif (is_array($config['connection']))
            {
                if (isset($config['connection']['hostname']))
                {
                    $hostname = $config['connection']['hostname'];
                }

                if (isset($config['connection']['port']))
                {
                    $port = $config['connection']['port'];
                }
            }

        }

        if (isset($config['timeout']))
        {
            $timeout = $config['timeout'];
        }

        if (isset($config['persistent']) AND $config['persistent'] === TRUE)
        {
            $connect = 'pconnect';
        }

        if (isset($config['database']))
        {
            $database = $config['database'];
        }

        if (isset($connect_with_socket))
        {
            $this->$connect($connect_with_socket, $timeout);
        }
        else
        {
            $this->$connect($hostname, $port, $timeout);
        }

        if (isset($config['password']))
        {
            if ( ! $this->auth($config['password']))
            {
                throw new RedisException('Access denied. Pls, check your password twice.');
            }
            $this->_config['password'] = str_repeat('*', strlen($config['password'])); // hide pass
        }

        $this->select($database);

        if (isset($config['prefix']))
        {
            $this->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }

        if (isset($config['serializer']) AND in_array($config['serializer'], ['none', 'php', 'igbinary']))
        {
            $this->setSerializer($config['serializer']);
        }

        self::$instances[$name] = $this;
    }

    final public function __destruct()
    {
        unset(self::$instances[$this->_instance]);

        return TRUE;
    }

    public function setSerializer($serializer)
    {
        $this->setOption(Redis::OPT_SERIALIZER, constant('Redis::SERIALIZER_' . strtoupper($serializer)));
    }

}

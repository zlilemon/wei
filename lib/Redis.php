<?php
/**
 * Wei Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Wei;

/**
 * A cache service that stored data in Redis
 *
 * @author      Twin Huang <twinhuang@qq.com>
 */
class Redis extends BaseCache
{
    /**
     * The redis object
     *
     * @var \Redis
     */
    protected $object;

    /**
     * The host, or the path to a unix domain socket
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * The port of the host
     *
     * @var int
     */
    protected $port = 6379;

    /**
     * The timeout seconds
     *
     * @var float|int
     */
    protected $timeout = 0.0;

    /**
     * Whether persistent connection or not
     *
     * @var bool
     */
    protected $persistent = true;

    /**
     * A password to authenticate the connection
     *
     * @var string
     */
    protected $auth;

    /**
     * The options for \Redis::setOptions()
     *
     * @var array
     */
    protected $options = array(
        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->connect();
    }

    /**
     * Returns the redis object, retrieve or store an item
     *
     * @param string $key The name of item
     * @param mixed $value The value of item
     * @param int $expire The expire seconds, defaults to 0, means never expired
     * @return mixed
     */
    public function __invoke($key = null, $value = null, $expire = 0)
    {
        switch (func_num_args()) {
            case 0:
                return $this->object;
            case 1:
                return $this->get($key);
            default:
                return $this->set($key, $value, $expire);
        }
    }

    /**
     * Connect the redis server and set redis options
     *
     * @return bool true on success, false on error
     */
    public function connect()
    {
        if ($this->object) {
            return true;
        }

        $this->object = new \Redis;
        $connect = $this->persistent ? 'pconnect' : 'connect';
        $result = $this->object->$connect($this->host, $this->port, $this->timeout);

        if ($result && $this->auth) {
            $result = $this->object->auth($this->auth);
        }

        if ($result) {
            foreach ($this->options as $key => $value) {
                $this->object->setOption($key, $value);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $expire = null, $fn = null)
    {
        $result = $this->object->get($this->prefix . $key);
        return $this->processGetResult($key, $result, $expire, $fn);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->object->set($this->prefix . $key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function getMulti(array $keys)
    {
        $keysWithPrefix = array();
        foreach ($keys as $key) {
            $keysWithPrefix[] = $this->prefix . $key;
        }
        return array_combine($keys, $this->object->mGet($keysWithPrefix));
    }

    /**
     * {@inheritdoc}
     *
     * Note:
     * 1. The "$expire" parameter is not support by redis MSET command
     * 2. The elements in returning values are all true or false, see links for more detail
     *
     * @link http://redis.io/commands/mset
     * @link https://github.com/nicolasff/phpredis/blob/master/redis_array.c#L844
     */
    public function setMulti(array $items, $expire = 0)
    {
        $keys = array_keys($items);
        $keysWithPrefix = array();
        foreach ($keys as $key) {
            $keysWithPrefix[] = $this->prefix . $key;
        }
        $items = array_combine($keysWithPrefix, $items);
        $result = $this->object->mset($items);
        return array_combine($keys, array_pad(array(), count($items), $result));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return (bool)$this->object->del($this->prefix . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return $this->object->exists($this->prefix . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value, $expire = 0)
    {
        $key = $this->prefix . $key;
        $result = $this->object->setnx($key, $value);
        if (true === $result) {
            $this->object->expire($key, $expire === 0 ? -1 : $expire);
        }
        return $result;
    }

    /**
     * Note: This method is not an atomic operation
     *
     * {@inheritdoc}
     */
    public function replace($key, $value, $expire = 0)
    {
        if (false === $this->object->get($this->prefix . $key)) {
            return false;
        }
        return $this->object->set($this->prefix . $key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function incr($key, $offset = 1)
    {
        return $this->object->incrBy($this->prefix . $key, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->object->flushAll();
    }

    /**
     * Get the redis object
     *
     * @return \Redis
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the redis object
     *
     * @param \Redis $object
     * @return $this
     */
    public function setObject(\Redis $object)
    {
        $this->object = $object;
        return $this;
    }
}

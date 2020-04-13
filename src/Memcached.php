<?php declare(strict_types=1);

namespace Mrself\Options;

class Memcached
{
    /**
     * @var \Memcached
     */
    private $memcached;

    public function __construct()
    {
        $this->memcached = new \Memcached();
        $result = $this->memcached->addServer('127.0.0.1', 11211);
        if (!$result) {
            throw new \RuntimeException('Cannot connect to memcached server');
        }
    }

    public function get(string $name)
    {
        return $this->memcached->get($name);
    }

    public function getDefault(string $name, $default)
    {
        $value = $this->get($name);
        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }
        return $value;
    }

    public function set(string $name, $value)
    {
        $this->memcached->set($name, $value);
    }
}
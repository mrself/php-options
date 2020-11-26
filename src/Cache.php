<?php declare(strict_types=1);

namespace Mrself\Options;

class Cache
{
    /**
     * @var Cache
     */
    private static $instance;

    /**
     * @var array
     */
    private $items = [];

    public function get(string $store, $key)
    {
        return $this->items[$store . $key];
    }

    public function has(string $store, $key): bool
    {
        return array_key_exists($store . $key, $this->items);
    }

    /**
     * @param string $store
     * @param string|int $key
     * @param $value
     * @return Cache
     */
    public function set(string $store, $key, $value)
    {
        $this->items[$store . $key] = $value;

        return $this;
    }

    public static function getInstance()
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    public function silentGet(string $store, $key)
    {
        if ($this->has($store, $key)) {
            return $this->get($store, $key);
        }

        return null;
    }

    public static function reset()
    {
        static::$instance = null;
    }
}
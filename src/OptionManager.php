<?php

namespace Armincms\Option;
  
use Armincms\Option\Contracts\Factory;
use Armincms\Option\Contracts\Store;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract; 
use InvalidArgumentException;

/**
 * @mixin \Armincms\Option\Contracts\Repository
 */
class OptionManager implements Factory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved option stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new Option manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get an option store instance by name, wrapped in a repository.
     *
     * @param  string|null  $name
     * @return \Armincms\Option\Contracts\Repository
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * Get an option driver instance.
     *
     * @param  string|null  $driver
     * @return \Armincms\Option\Contracts\Repository
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Attempt to get the store from the local option.
     *
     * @param  string  $name
     * @return \Armincms\Option\Contracts\Repository
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return \Armincms\Option\Contracts\Repository
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Option store [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }  

    /**
     * Create an instance of the file option driver.
     *
     * @param  array  $config
     * @return \Armincms\Option\Repository
     */
    protected function createFileDriver(array $config)
    {
        return $this->repository(new FileStore($this->app['files'], $config['path']));
    } 

    /**
     * Create an instance of the Null option driver.
     *
     * @return \Armincms\Option\Repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    } 

    /**
     * Create an instance of the database option driver.
     *
     * @param  array  $config
     * @return \Armincms\Option\Repository
     */
    protected function createDatabaseDriver(array $config)
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);

        return $this->repository(
            new DatabaseStore($connection, $config['table'])
        );
    } 

    /**
     * Create a new option repository with the given implementation.
     *
     * @param  \Armincms\Option\Contracts\Store  $store
     * @return \Armincms\Option\Repository
     */
    public function repository(Store $store)
    {
        return new Repository($store); 
    } 

    /**
     * Get the option connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["option.stores.{$name}"];
    }

    /**
     * Get the default option driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['option.default'];
    }

    /**
     * Set the default option driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['option.default'] = $name;
    }

    /**
     * Unset the given driver instances.
     *
     * @param  array|string|null  $name
     * @return $this
     */
    public function forgetDriver($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        foreach ((array) $name as $optionName) {
            if (isset($this->stores[$optionName])) {
                unset($this->stores[$optionName]);
            }
        }

        return $this;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    { 
        return $this->store()->$method(...$parameters);
    }
}

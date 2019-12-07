<?php

namespace Armincms\Option;

use ArrayAccess;
use BadMethodCallException; 
use Armincms\Option\Contracts\Repository as RepositoryContract;
use Armincms\Option\Contracts\Store; 
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Collection;

/**
 * @mixin \Armincms\Option\Contracts\Store
 */
class Repository implements ArrayAccess, RepositoryContract
{  
    use Macroable {
        __call as macroCall;
    }

    /**
     * The option store implementation.
     *
     * @var \Armincms\Option\Contracts\Store
     */
    protected $store; 

    /**
     * Create a new option repository instance.
     *
     * @param  \Armincms\Option\Contracts\Store  $store
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    } 

    /**
     * Determine if an option exists in the option.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return ! is_null($this->get($key));
    }   
       
    /**
     * Retrieve items from the storage by the tag name. 
     * 
     * @param  string $tag
     * @return array
     */
    public function tag(string $tag) : array
    {
        return $this->store->tags($tag);
    }

    /**
     * Retrieve an option from the storage.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    { 
        $value = $this->store->get($this->optionKey($key));  

        return is_null($value) ? value($default) : $value;
    }

    /**
     * Retrieve multiple options from the storage by key.
     *
     * Items not found in the option will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys)
    {
        $values = $this->store->many(collect($keys)->map(function ($value, $key) {
            return is_string($key) ? $key : $value;
        })->values()->all());

        return collect($values)->map(function ($value, $key) use ($keys) {
            return $this->handleManyResult($keys, $key, $value);
        })->all();
    } 

    /**
     * Handle a result for the "many" method.
     *
     * @param  array  $keys
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function handleManyResult($keys, $key, $value)
    {
        // If we could not find the option value, we will get the default value 
        // for this option value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) { 
            return isset($keys[$key]) ? value($keys[$key]) : null;
        } 

        return $value;
    }

    /**
     * Store an option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string $tag
     * @return bool
     */
    public function put(string $key, $value, string $tag = null) : bool  
    {    
        return $this->store->put($this->optionKey($key), $value, $tag); 
    }  

    /**
     * Store multiple options into storage.
     *
     * @param  array $values
     * @param  int   $seconds
     * @return bool
     */
    public function putMany(array $values, string $tag = null): bool
    { 
        return Collection::make($values)->filter(function($value, $key) use ($tag) {
            return $this->put($key, $value, $tag);
        })->count() === count($values); 
    }  

    /**
     * Retrieve an option from the storage and delete it.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull(string $key, $default = null)
    {
        return tap($this->get($key, $default), function () use ($key) {
            $this->delete($key);
        });
    } 

    /**
     * Retrieve options from storage by tag name and delete them.
     *
     * @param  string  $tag 
     * @return array
     */
    public function pullTag(string $tag, array $defaults = []) : array
    {
        $data = $this->store->tags($tag); 

        return Collection::make($data)->map(function($value, $key) use ($defaults) {
            return $this->pull($key, $defaults[$key] ?? null); 
        })->toArray();
    }

    /**
     * Remove an option from the storage.
     *
     * @param  string  $key
     * @return bool
     */ 
    public function delete($key)
    {
        return $this->store->delete($this->optionKey($key));
    } 

    /**
     * Format the option key for storage.
     *
     * @param  string  $key
     * @return string
     */
    protected function optionKey($key)
    {
        return $key;
    }   

    /**
     * Get the option store implementation.
     *
     * @return \Armincms\Option\Contracts\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Determine if a option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Retrieve an option from the storage by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Store an option in the storge without tag.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->put($key, $value, $this->default);
    }

    /**
     * Remove an option from the storage.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    } 

    /**
     * Handle dynamic calls into macros or pass missing methods to the store.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->store->$method(...$parameters);
    }

    /**
     * Dynamically get option.
     * 
     * @param  string $key 
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    } 

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    } 
}

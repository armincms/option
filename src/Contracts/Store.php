<?php

namespace Armincms\Option\Contracts;

interface Store
{   
    /**
     * Retrieve all stored items.
     * 
     * @return array
     */
    public function all() : array;

    /**
     * Retrieve multiple items from the storage by key.
     *
     * Items not found in the storage will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys) : array; 

    /**
     * Retrieve multiple items from the storage by tag.
     *
     * Items not found in the storage will have a null value.
     *
     * @param  string  $tag
     * @return array
     */
    public function tags(string $tag) : array; 
    
    /**
     * Retrieve a data from the storage by key.
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key); 
    
    /**
     * Store a data in the storage.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string $tag
     * @return bool
     */
    public function put(string $key, $value, string $tag = null) : bool;

    /**
     * Remove an option from storage.
     *
     * @param  string  $key
     * @return bool
     */
    public function delete(string $key);  
}

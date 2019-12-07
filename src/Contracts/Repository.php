<?php

namespace Armincms\Option\Contracts;
  

interface Repository  
{ 
    /**
     * Retrieve items from the storage by the tag name. 
     * 
     * @param  string $tag
     * @return array
     */
    public function tag(string $tag) : array;

    /**
     * Retrieve an option from the storage.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null); 

    /**
     * Store an option.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string $tag
     * @return bool
     */
    public function put(string $key, $value, string $tag = null) : bool;  

    /**
     * Store multiple options into storage.
     *
     * @param  array $values
     * @param  int   $seconds
     * @return bool
     */
    public function putMany(array $values, string $tag = null): bool;  

    /**
     * Retrieve an option from the storage and delete it.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull(string $key, $default = null);

    /**
     * Retrieve options from storage by tag name and delete them.
     *
     * @param  string  $tag 
     * @return array
     */
    public function pullTag(string $tag, array $defaults = []) : array; 
}

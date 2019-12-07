<?php

namespace Armincms\Option;

use Exception;
use Armincms\Option\Contracts\Store;
use Illuminate\Filesystem\Filesystem; 
use Illuminate\Support\Collection; 

class FileStore implements Store
{ 
    /**
     * The Illuminate Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The file option directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Create a new file option store instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $directory
     * @return void
     */
    public function __construct(Filesystem $files, $directory)
    {
        $this->files = $files;
        $this->directory = $directory;
    } 

    /**
     * Retrieve all stored items.
     * 
     * @return array
     */
    public function all() : array
    {
        return $this->getPayloads()->map->value->toArray();
    }

    /**
     * Retrieve multiple items from the storage by key.
     *
     * Items not found in the storage will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys) : array
    {
        return $this->getPayloads()->only($keys)->map->value->toArray();
    }

    /**
     * Retrieve multiple items from the storage by tag.
     *
     * Items not found in the storage will have a null value.
     *
     * @param  string  $tag
     * @return array
     */
    public function tags(string $tag) : array
    {
        return $this->getPayloads()->where('tag', $tag)->map->value->toArray();
    } 

    /**
     * Retrieve an item from the storage by key.
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getPayload($key)['value'] ?? null;  
    }

    /**
     * Store a data in the storage.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string $tag
     * @return bool
     */
    public function put(string $key, $value, string $tag = null) : bool
    {
        $this->ensureOptionDirectoryExists($path = $this->path($key));  

        $merged = $this->getPayloads()->put($key, compact('tag', 'value', 'key'))->toArray();

        return false !== $this->files->put($path, serialize($merged), true);
    }
    
    /**
     * Remove an item from the option.
     *
     * @param  string  $key
     * @return bool
     */
    public function delete(string $key)
    {
        if($data = $this->getPayloads()) {
            unset($data[$key]); 
            
            return false !== $this->files->put($this->path(), serialize($data), true);
        } 

        return true;
    } 

    /**
     * Create the file option directory if necessary.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureOptionDirectoryExists($path)
    {
        if (! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    } 
 
    /**
     * Retrieve an item from the option by key.
     *
     * @param  string  $key
     * @return array
     */
    protected function getPayload($key)
    {
        return $this->getPayloads()->get($key);
    } 
 
    /**
     * Retrieve stored items.
     *
     * @param  string  $key
     * @return \Illuminate\Support\Collection
     */
    protected function getPayloads() : Collection
    { 
        // If the file doesn't exist, we obviously cannot return the option so we will
        // just return null
        try {
            $contents = $this->files->get($this->path(), true);
        } catch (Exception $e) {
            return $this->emptyPayload();
        }
 

        try {
            return Collection::make(unserialize($contents));
        } catch (Exception $e) { 
            return $this->emptyPayload();
        }  
    }

    /**
     * Get a default empty payload for the option.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function emptyPayload() : Collection
    {
        return Collection::make([]);
    }

    /**
     * Get the full path for the given option key.
     *
     * @param  string  $key
     * @return string
     */
    protected function path(string $key = null)
    {  
        return "{$this->directory}/options";//. ($key ? "{$key}" : 'option');
    }  

    /**
     * Get the Filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get the working directory of the option.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    } 
}

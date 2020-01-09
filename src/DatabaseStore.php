<?php

namespace Armincms\Option;

use Closure;
use Exception;
use Armincms\Option\Contracts\Store;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\PostgresConnection; 
use Illuminate\Support\Str;

class DatabaseStore implements Store
{ 
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The name of the option table.
     *
     * @var string
     */
    protected $table; 

    /**
     * Create a new database store.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  string  $prefix
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table)
    {
        $this->table = $table; 
        $this->connection = $connection;
    }

   /**
     * Retrieve all stored items.
     * 
     * @return array
     */
    public function all() : array
    {  
        return $this->handleManyResult( $this->table()->get() );
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
        return $this->handleManyResult( $this->table()->whereIn('key', $keys)->get()  );
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
        return $this->handleManyResult( $this->table()->where('tag', '=', $tag)->get() );
    } 

    /**
     * Retrieve an item from the storage by key.
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key)
    {  
        $option = $this->table()->where('key', '=', $key)->first();  
        $option = is_array($option)? (object) $option : $option;

        return is_null($option) ? null : $this->unserialize($option->value);
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
        if($this->has($key)) {
            return $this->update($key, $value, $tag);
        }

       try { 
            return $this->table()->insert([
                'key'   => $key,
                'value' => $this->serialize($value), 
                'tag'   => $tag
            ]);
        } catch (Exception $e) { 
            return false;
        }  
    }
    
    /**
     * Update the existence option value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string $tag
     * @return bool
     */
    public function update(string $key, $value, string $tag = null) : bool
    {
        try { 
            return $this->table()->where('key', '=', $key)->update([ 
                'value' => $this->serialize($value), 
                'tag'   => $tag
            ]);
        } catch (Exception $e) { 
            return false;
        }
    }

    /**
     * Indicate that the option exists.
     *
     * @param  string $key 
     * @return bool
     */
    public function has(string $key) : bool
    { 
        return $this->table()->where('key', '=', $key)->count() > 0;
    }
    
    /**
     * Remove an item from the option.
     *
     * @param  string  $key
     * @return bool
     */
    public function delete(string $key)
    { 
        return $this->table()->where('key', '=', $key)->delete();
    }  
    
    /**
     * Get a query builder for the option table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the underlying database connection.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Serialize the given value.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function serialize($value)
    {
        $result = serialize($value);

        if ($this->connection instanceof PostgresConnection && Str::contains($result, "\0")) {
            $result = base64_encode($result);
        }

        return $result;
    }

    /**
     * Unserialize the given value.
     *
     * @param  string  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        if ($this->connection instanceof PostgresConnection && ! Str::contains($value, [':', ';'])) {
            $value = base64_decode($value);
        }

        return unserialize($value);
    }

    /**
     * Handle a result for the "all" and "tag" method.
     *
     * @param  \Illuminate\Support\Collection  $results 
     * @return array
     */
    protected function handleManyResult($results) : array
    {
        return $results->pluck('value', 'key')->map(function($value) {
            return $this->unserialize($value);
        })->toArray();
    }
}

<?php      
if (! function_exists('option')) { 
    /**
     * Add Or Retrieve Option.
     *
     * @param  string  $key
     * @param  mixed $default
     * @return mixed $value
     */
    function option($key = null, $default = null)
    {     
        if(! is_null($key)) { 
            return app('armincms.option')->get($key, $default);  
        }

        return app('armincms.option')->store();
    }
}  

if (! function_exists('option_exists')) { 
    /**
     * Check existance of option.
     *
     * @param  string  $key
     * @return boolean
     */
    function option_exists(string $key)
    {     
    	return option()->has($key); 
    }
} 
   
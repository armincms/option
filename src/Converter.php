<?php 
namespace Armincms\Option;

use Armincms\Option\Item;   

class Converter 
{
    /**
     * Save new option.
     * 
     * @param  \Armincms\Option\Item $item 
     * @return $this
     */
    public function detect($value)
    {
        if(is_integer($value) || is_numeric($value) && (int) $value == $value) { 
            return 'integer';
            $value = (int) $value;
        } else if(is_bool($value) || $value === 'true' || $value === 'false') {
            return 'boolean';
            $value = (boolean) $value ? 'true' : 'false';
        } else if(is_float($value)) { 
            return 'float';
            $value = floatval($value);
        } else if(is_double($value)) { 
            return 'double';
            $value = doubleval($value);
        } else if(is_string($value) && strtotime($value)) { 
            return 'datetime';
        } else if (is_array($value)) {
            return 'array';
            $value = json_encode($value); 
        } elseif ($value instanceof Arrayable) {
            return 'object';
            $value = json_encode($value->toArray());
        } elseif ($value instanceof Jsonable) {
            return 'object';
            $value = $value->toJson();
        } elseif ($value instanceof JsonSerializable) {
            return 'object';
            $value = $value->jsonSerialize();
        } elseif (empty($value)) {
            return 'null';
            $value = null;
        } 

        return 'original';  
    } 

    /**
     * Retrive stored option.
     * 
     * @param string $key
     * @return \Armincms\OptionItem 
     */
    public function serialize($value) : array
    {
        switch ($type) {
            case 'array':  
            case 'object':
                return Collection::make(json_decode($data['value'], true)); 
                break;
            case 'boolean': 
                return (boolean) $value; 
                break;
            case 'integer': 
                return (int) $value; 
                break;
            case 'float': 
                return floatval($value); 
                break;
            case 'double': 
                return doubleval($value); 
                break;
            case 'null': 
                return null; 
                break;
            case 'datetime': 
                return Carbon::parse($value);
                break;
            
            default:
                return (string) $value;
                break;
        } 

    } 
}

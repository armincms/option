# Option   
A key-value storage for laravel 

##### Table of Contents   

* [Introduction](#introduction)      
* [Installation](#installation)      
* [Configuration](#configuration)           
* [Single Storing](#single-storing)          
* [Mass Storing](#mass-storing)            
* [Grouped Data](#grouped-data)           
* [Retrieving](#retrieving)           
* [Retrieving From Other Storage](#retrieving-from-other-storage)   
* [Check For Existence](#check-for-existence)   



## Introduction
Armincms Option is a package for store `key-value`. with this package, you can store values by `key` and `tag` in the simplest way into multiple storages.

## Installation

To get started with Armincms Option, first run:

```
    composer require armincms/option
```

Then publish configuration:

```
    php artisan vendor:publish --tag=armincms.option
```

This command publishes `config` and `migration` file into the appropriate path.

## Configuration

This package supports `file` and `database` storage for storing data.
The default storage is `file`. for change the storage type you have two way:

* With `.env` file: `OPTION_DRIVER=database` 
* With `Config` respository: `Config::set('option.default', 'file')`
 
**Attention 1:**
	*if you want use database storage you should run `php artisan migrate` in console.*
	
**Attention 2:**
	*For access to `option-manager` by laravel container you can use `app('armincms.option')`*
	
**Attention 3:**
	*For simple coding: you can use helper `option()` insteadof `app('armincms.option')`*


### Single Storing

There exists two way for storing single data:

* first:
	`option()->put(key, value)`

* second: 
	`option()->key = value` 
	
	

### Mass Storing 

For mass storing data use the followings method: 

	option()->putMany([
		key1 => value1,
		key2 => value2,
	])  
	
	

### Grouped Data 

For grouping many option, can pass `tag` parameter when storing a data: 

	app('armincms.option')->put(key, value, tag)

Also; it's possible to attach a tag into data when mass storing:

	app('armincms.option')->putMany([
		key1 => value1,
		key2 => value2,
	], tag)



### Retrieving

There exist many ways to retrieve your data.you can retrieve your data, `single` or `multiple`.

*single retrieving:*

To retrieve an option, you can use `option()->key`. but if you need `default` value for missed values;  you can use `option(key, default)` or `option()->get(key, default)` . 

*multiple retrieving:*

Also, retrieving multiple options is not difficult. you can retrieve many values by its keys with the `many` method; like  `option()->many(keys)`.
If you need default value for missed values; you can pass an associative array of `keys` and `default values`; like following:

```
	option()->many([
		key1 => key1-default, 
		key2 => key2-default,
		key3,
		key4,
		key5 => key5-default
	])
```

And you can retrieve `tagged` values with `option()->tag(tag)`. 



### Retrieving From Other Storage

For store an option into `none default` driver with assumption that default 
driver is `database`; follow this:

	app('armincms.option')->store('file')->put(key, value, tag)
	app('armincms.option')->store('file')->many([key1, key2], tag)

For retrieve you can use this: 

	app('armincms.option')->store('file')->get(key, default)
	app('armincms.option')->store('file')->tag(tag) 
	app('armincms.option')->store('file')->many(keys)  


### Check For Existence
If you want to check for existance of an option ; you can use helper `option_exists(key)` or `option()->has(key)`.
 

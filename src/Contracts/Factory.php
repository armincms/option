<?php

namespace Armincms\Option\Contracts;

interface Factory
{
    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return \Armincms\Option\Contracts\Repository
     */
    public function store($name = null);
}

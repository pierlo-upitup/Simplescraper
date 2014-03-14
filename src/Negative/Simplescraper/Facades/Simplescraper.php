<?php namespace Negative\Simplescraper\Facades;

use Illuminate\Support\Facades\Facade;

class Simplescraper extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'simplescraper'; }

}
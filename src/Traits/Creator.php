<?php

namespace Sunnysideup\UpgradeSilverstripe\Traits;

trait Creator
{
    /**
     * Holds the only instance of me
     * @var mixed
     */
    protected static $singleton = null;

    public function destroy()
    {
        self::$singleton = null;
    }

    /**
     * Create the only instance of me and return it
     * @return mixed
     */
    public static function create()
    {
        if (self::$singleton === null) {
            $className = static::class;
            self::$singleton = new $className();
        }
        return self::$singleton;
    }
}

<?php

namespace Namest\Facebook\Traits;

use Carbon\Carbon;

/**
 * Trait HasCreatedTime
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Traits
 *
 * @property array attributes
 *
 */
trait HasCreatedTime
{
    /**
     * @param string $time
     *
     * @return Carbon
     */
    public function getCreatedTimeAttribute($time)
    {
        return new Carbon($time);
    }

    /**
     * Normalize datetime string
     *
     * @param string $time
     *
     * @return Carbon
     */
    public function setCreatedTimeAttribute($time)
    {
        if ( ! $time instanceof Carbon)
            $time = new Carbon($time);

        $this->attributes['updated_time'] = $time->toIso8601String();
    }
}

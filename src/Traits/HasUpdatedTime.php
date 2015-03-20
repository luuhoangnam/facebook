<?php

namespace Namest\Facebook\Traits;

use Carbon\Carbon;

/**
 * Trait HasUpdatedTime
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Traits
 *
 * @property array attributes
 *
 */
trait HasUpdatedTime
{
    /**
     * @param string $time
     *
     * @return Carbon
     */
    public function getUpdatedTimeAttribute($time)
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
    public function setUpdatedTimeAttribute($time)
    {
        if ( ! $time instanceof Carbon)
            $time = new Carbon($time);

        $this->attributes['updated_time'] = $time->toIso8601String();
    }
}

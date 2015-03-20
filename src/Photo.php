<?php

namespace Namest\Facebook;

use Namest\Facebook\Traits\HasComments;
use Namest\Facebook\Traits\HasCreatedTime;
use Namest\Facebook\Traits\HasUpdatedTime;

/**
 * Class Photo
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Photo extends Object
{
    use HasComments, HasCreatedTime, HasUpdatedTime;

    protected $fields = [
        'id',
        'from',
        'height',
        'width',
        'icon',
        'images',
        'created_time',
        'updated_time',
    ];

    /**
     * @param string $profile Profile class
     *
     * @return EdgeOut
     */
    public function uploader($profile = null)
    {
        if ( ! is_null($profile))
            if ( ! (new $profile) instanceof Profile)
                throw new \InvalidArgumentException("[{$profile}] class must be inheritance from Namest\\Facebook\\Profile");

        if (is_null($profile))
            $profile = Profile::class;

        return $this->belongsTo($profile, 'UPLOADED', null, Edge::OUT);
    }
}

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
     * @param string $type Profile class
     *
     * @return EdgeOut
     */
    public function uploader($type = null)
    {
        $profile = $this->makeProfileFromClassName($type);

        return $this->belongsTo($profile, 'UPLOADED', null, Edge::OUT);
    }
}

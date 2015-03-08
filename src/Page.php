<?php

namespace Namest\Facebook;

/**
 * Class Page
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Page extends Profile
{
    /**
     * @return EdgeOut
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'OWN');
    }

    protected $fields = [
        'id',
        'about',
        'category',
        'name',
        'username',
        'website',
        'phone',
    ];
}

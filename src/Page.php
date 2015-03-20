<?php

namespace Namest\Facebook;

use Illuminate\Support\Collection;

/**
 * Class Page
 *
 * @property-read Collection posts
 * @property string          avatar
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Page extends Profile
{
    protected $fields = [
        'id',
        'about',
        'category',
        'name',
        'username',
        'website',
        'phone',
        'picture',
    ];

    /**
     * @return EdgeOut
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'ON', 'posts', Edge::OUT);
    }

    /**
     * @return array
     */
    public function getFetchPostsFields()
    {
        return [
            'id',
            'from' => ['id', 'name', 'category'],
            'type',
            'message',
            'created_time',
            'updated_time',
        ];
    }
}

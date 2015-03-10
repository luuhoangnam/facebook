<?php

namespace Namest\Facebook;

use Illuminate\Support\Collection;

/**
 * Class Page
 *
 * @property-read Collection posts
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
    ];

    /**
     * @return EdgeOut
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'OWN', 'posts', Edge::IN);
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

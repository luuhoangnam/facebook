<?php

namespace Namest\Facebook;

/**
 * Class Post
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Post extends Object
{
    /**
     * @return EdgeOut
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'ON', 'comments', Edge::IN);
    }

    /**
     * @return array
     */
    public function getFetchCommentsParameters()
    {
        return ['filter' => 'stream'];
    }

    /**
     * @return array
     */
    public function getFetchCommentsFields()
    {
        return [
            'id',
            'from',
            'message',
            'can_remove',
            'can_hide',
            'parent' => ['id'],
        ];
    }
}

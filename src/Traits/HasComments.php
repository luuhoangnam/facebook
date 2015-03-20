<?php

namespace Namest\Facebook\Traits;

use Namest\Facebook\Comment;
use Namest\Facebook\Edge;
use Namest\Facebook\EdgeOut;

/**
 * Trait HasComments
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Traits
 *
 */
trait HasComments
{
    /**
     * @return EdgeOut
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'ON', 'comments', Edge::OUT);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function comment($message)
    {
        return $this->comments()->publish(['message' => $message]);
    }
}

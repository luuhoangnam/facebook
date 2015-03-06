<?php

namespace Namest\Facebook;

/**
 * Class Comment
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Comment extends Object
{
    /**
     * @return EdgeOut
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'ON', 'comments', Edge::IN);
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

    /**
     * Alias method
     *
     * @return EdgeOut
     */
    public function replies()
    {
        return $this->comments();
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function reply($message)
    {
        return $this->comment($message);
    }

    /**
     * @return array
     */
    public function getFetchRepliesParameters()
    {
        return [
            'limit' => 100,
        ];
    }

    /**
     * @return array
     */
    public function getFetchRepliesFields()
    {
        return [
            'id',
            'from',
            'message',
            'can_remove',
            'can_hide',
        ];
    }
}

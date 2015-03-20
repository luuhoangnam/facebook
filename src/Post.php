<?php

namespace Namest\Facebook;

use Illuminate\Support\Collection;
use Namest\Facebook\Traits\HasComments;
use Namest\Facebook\Traits\HasCreatedTime;
use Namest\Facebook\Traits\HasFromField;
use Namest\Facebook\Traits\HasUpdatedTime;

/**
 * Class Post
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 * @property Collection comments
 * @property Profile    publisher
 * @property Page       page
 * @property string     message
 *
 */
class Post extends Object
{
    use HasComments, HasFromField, HasCreatedTime, HasUpdatedTime;

    protected $fields = [
        'id',
        'from' => ['id', 'name', 'category'],
        'type',
        'message',
        'created_time',
        'updated_time',
    ];

    /**
     * @param string $profile Profile class
     *
     * @return EdgeOut
     */
    public function publisher($profile = null)
    {
        if ( ! is_null($profile))
            if ( ! (new $profile) instanceof Profile)
                throw new \InvalidArgumentException("[{$profile}] class must be inheritance from Namest\\Facebook\\Profile");

        if (is_null($profile))
            $profile = Profile::class;

        return $this->belongsTo($profile, 'PUBLISHED', null, Edge::OUT);
    }

    /**
     * @return EdgeOut
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'ON', false, Edge::IN);
    }

    /**
     * @return array
     */
    public function getFetchCommentsParameters()
    {
        return [
            'filter' => 'stream',
            'limit'  => 100,
        ];
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
            'parent' => ['id', 'from', 'message', 'can_remove', 'can_hide', 'can_like'],
            'can_remove',
            'can_hide',
            'can_like',
        ];
    }
}

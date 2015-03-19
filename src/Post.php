<?php

namespace Namest\Facebook;

use Illuminate\Support\Collection;

/**
 * Class Post
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 * @property Collection comments
 * @property Page       page
 * @property string     message
 *
 */
class Post extends Object
{
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
    public function publisher($profile)
    {
        if ( ! (new $profile) instanceof Profile)
            throw new \InvalidArgumentException("[{$profile}] class must be inheritance from Namest\\Facebook\\Profile");

        return $this->belongsTo($profile, 'PUBLISHED', null, Edge::OUT);
    }

    /**
     * @return EdgeOut
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'PUBLISHED', false, Edge::OUT);
    }

    /**
     * @return EdgeOut
     */
    public function comments()
    {
        $options = [
            'cast' => Edge::COLLECTION,
        ];

        return $this->hasMany(Comment::class, 'ON', 'comments', Edge::OUT, $options);
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

    /**
     * @param mixed $data
     */
    public function hydrateFromField($data)
    {
        if ( ! in_array('from', $this->fields) && ! array_key_exists('from', $this->fields))
            throw new \LogicException("From fields must be provided");

        $client  = $this->getClient();
        $profile = $client->newProfileFromData($data);

        /** @var Profile $profile */
        $attributes = (array) $data;
        $profile    = $profile->fill($attributes)->sync();

        $this->saved(function () use ($profile) {
            // Make edge relation
            // (profile:Profile)-[:PUBLISH]->(post:Post)-[:ON]->(page:Page)
            $this->publisher(get_class($profile))->save($profile);

            $this->unsetEvent('saved');
        });
    }
}

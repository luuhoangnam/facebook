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
    protected $fields = [
        'id',
        'from',
        'message',
        'created_time',
        'can_hide',
        'can_like',
        'can_remove',
    ];

    /**
     * Relation to profile who make this comment
     *
     * @param string $profile Profile class
     *
     * @return EdgeOut
     */
    public function owner($profile)
    {
        if ( ! (new $profile) instanceof Profile)
            throw new \InvalidArgumentException("[{$profile}] class must be inheritance from Namest\\Facebook\\Profile");

        return $this->belongsTo($profile, 'LEAVE', false, Edge::OUT);
    }

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
     * @param string $mesage New message
     */
    public function edit($mesage)
    {
        $this->update(['message' => $mesage]);
    }

    public function hide()
    {
        $this->update(['is_hidden' => true]);
    }

    public function unhide()
    {
        $this->update(['is_hidden' => false]);
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
            'created_time',
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
        $client  = $this->getClient();
        $profile = $client->newProfileFromData($data);

        /** @var Profile $profile */
        $properties = [];
        foreach ((array) $data as $key => $value) {
            if ($key === 'category_list')
                continue;

            $properties[$key] = $value;
        }

        $profile->fill($properties)->save();

        $this->saved(function () use ($profile) {
            // TODO Make edge relation
            // (profile:Profile)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)
            $this->owner(get_class($profile))->save($profile);

            $this->unsetEvent('saved');
        });
    }
}

<?php

namespace Namest\Facebook;

use Namest\Facebook\Traits\HasComments;
use Namest\Facebook\Traits\HasCreatedTime;

/**
 * Class Comment
 *
 * @property Profile owner
 * @property Post    object
 * @property Post    post
 * @property string  message
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Comment extends Object
{
    use HasComments, HasCreatedTime;

    protected $fields = [
        'id',
        'from',
        'message',
        'attachment',
        'is_hidden',
        'can_hide',
        'can_like',
        'can_remove',
        'created_time',
    ];

    /**
     * Relation to profile who make this comment
     *
     * @param string $type Profile class
     *
     * @return EdgeOut
     */
    public function owner($type = null)
    {
        $profile = $this->makeProfileFromClassName($type);

        return $this->belongsTo($profile, 'LEAVE', false, Edge::OUT);
    }

    /**
     * @return EdgeIn
     */
    public function object()
    {
        $options = [
            'cast' => Edge::SINGLE,
        ];

        return $this->belongsTo(Post::class, 'ON', false, Edge::IN, $options);
    }

    /**
     * @return EdgeOut
     */
    public function attachment()
    {
        return $this->hasOne(Photo::class, 'ATTACHED_TO', false, Edge::OUT);
    }

    /**
     * @return EdgeOut
     */
    public function post()
    {
        return $this->object();
    }

    /**
     * @return EdgeIn
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'HAS_PARENT', null, Edge::IN);
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
     *
     * @return bool
     */
    public function edit($mesage)
    {
        return $this->update(['message' => $mesage]);
    }

    /**
     * @return bool
     */
    public function hide()
    {
        return $this->update(['is_hidden' => true]);
    }

    /**
     * @return bool
     */
    public function unhide()
    {
        return $this->update(['is_hidden' => false]);
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
        $properties = array_filter((array) $data, function ($key) {
            return ! in_array($key, ['category_list']);
        }, ARRAY_FILTER_USE_KEY);

        if ( ! array_key_exists('id', $properties))
            throw new \LogicException("Can not fetch profile information for this comment if profile id does not appear");

        $profile->setId($properties['id'])->sync();

        $this->saved(function () use ($profile) {
            // TODO Make edge relation
            // (profile:Profile)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)
            $this->owner(get_class($profile))->save($profile);

            $this->unsetEvent('saved');
        });
    }

    /**
     * @param mixed $data
     */
    public function hydrateParentField($data)
    {
        $properties = (array) $data;

        $this->saved(function () use ($properties) {
            // TODO Make edge relation
            // (profile:Profile)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)

            $comment = new Comment($properties);
            $comment->save();

            $this->parent()->save($comment);

            $this->unsetEvent('saved');
        });
    }

    /**
     * @param \StdClass $data
     */
    public function hydrateAttachmentField($data)
    {
        // Temporarily
        if ($data->type != 'photo')
            return;

        $photoID = $data->target->id;

        $photo = new Photo;
        $photo->setId($photoID);
        $photo->sync();

        $this->saved(function () use ($photo) {
            $this->attachment()->save($photo);

            $this->unsetEvent('saved');
        });
    }
}

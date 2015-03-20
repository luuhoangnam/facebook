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
    use HasComments, HasCreatedTime, HasUpdatedTime;

    protected $fields = [
        'id',
        'from' => ['id', 'name', 'category'],
        'type',
        'message',
        'created_time',
        'updated_time',
    ];

    /**
     * @param string $type Profile class
     *
     * @return EdgeOut
     */
    public function publisher($type = null)
    {
        $profile = $this->makeProfileFromClassName($type);

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

    /**
     * @param mixed $data
     */
    public function hydrateFromField($data)
    {
        $client  = $this->getClient();
        $profile = $client->newProfileFromData($data);

        /** @var Profile $profile */
        /** @noinspection PhpUndefinedConstantInspection */
        $properties = array_filter((array) $data, function ($key) {
            return ! in_array($key, ['category_list']);
        }, ARRAY_FILTER_USE_KEY);

        if ( ! array_key_exists('id', $properties))
            throw new \LogicException("Can not fetch profile information for this comment if profile id does not appear");

        $profile->setId($properties['id']);

        if (is_null($profile->get()))
            $profile->sync();

        $this->saved(function () use ($profile) {
            // Make edge relation
            // (profile:Profile)-[:PUBLISH]->(post:Post)-[:ON]->(page:Page)
            $this->publisher(get_class($profile))->save($profile);

            $this->unsetEvent('saved');
        });
    }

    /**
     * @param string $type
     */
    public function hydrateTypeField($type)
    {
        switch ($type) {
            case 'photo':
                $this->hydratePhotoTypePost();
                break;
        }
    }

    private function hydratePhotoTypePost()
    {

    }
}

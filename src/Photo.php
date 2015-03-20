<?php

namespace Namest\Facebook;

use Namest\Facebook\Traits\HasComments;
use Namest\Facebook\Traits\HasCreatedTime;
use Namest\Facebook\Traits\HasUpdatedTime;

/**
 * Class Photo
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Photo extends Object
{
    use HasComments, HasCreatedTime, HasUpdatedTime;

    protected $fields = [
        'id',
        'from',
        'height',
        'width',
        'icon',
        'images',
        'created_time',
        'updated_time',
    ];

    /**
     * @param string $type Profile class
     *
     * @return EdgeOut
     */
    public function uploader($type = null)
    {
        $profile = $this->makeProfileFromClassName($type);

        return $this->belongsTo($profile, 'UPLOADED', null, Edge::OUT);
    }

    /**
     * @param array $images
     */
    public function setImagesAttribute($images)
    {
        if ( ! ($encodedString = json_encode($images)))
            $encodedString = serialize($images);

        $this->attributes['images'] = $encodedString;
    }

    /**
     * @param string $encodedString
     *
     * @return array
     */
    public function getImagesAttribute($encodedString)
    {
        if ( ! ($images = json_decode($encodedString)))
            $images = unserialize($encodedString);

        return $images;
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
            throw new \LogicException("Can not fetch profile information for this photo if profile id does not appear");

        $profile = $profile->findOrSync($properties['id']);

        $this->saved(function () use ($profile) {
            // TODO Make edge relation
            // (profile:Profile)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)
            $this->uploader(get_class($profile))->save($profile);

            $this->unsetEvent('saved');
        });
    }
}

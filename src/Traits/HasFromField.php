<?php

namespace Namest\Facebook\Traits;

/**
 * Trait HasFromField
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Traits
 *
 */
trait HasFromField
{
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

<?php

namespace Namest\Facebook\Traits;

use Everyman\Neo4j\Node;

/**
 * Trait HasPicture
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Traits
 *
 * @property string avatar
 *
 * @method void saved(callable $handler)
 * @method Node save()
 * @method void unsetEvent($event)
 */
trait HasPicture
{
    /**
     * @param \StdClass $data
     */
    public function hydratePictureField($data)
    {
        $avatar = $data->data->url;

        $this->saved(function () use ($avatar) {

            $this->avatar = $avatar;
            $this->save();

            $this->unsetEvent('saved');
        });
    }
}

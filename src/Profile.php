<?php

namespace Namest\Facebook;

/**
 * Class Profile
 *
 * @property string avatar
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Profile extends Object
{
    const USER = 'user';
    const PAGE = 'page';
    const GROUP = 'group';
    const EVENT = 'event';
    const APPLICATION = 'application';

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

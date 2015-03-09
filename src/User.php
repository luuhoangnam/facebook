<?php

namespace Namest\Facebook;

use Illuminate\Support\Collection;

/**
 * Class User
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 * @property-read Collection accounts
 *
 */
class User extends Profile
{

    /**
     * @return EdgeOut
     */
    public function accounts()
    {
        $options = [
            'saving' => [
                'relation' => ['access_token', 'perms'],
                'end'      => ['id', 'name', 'category'],
            ],
        ];

        return $this->hasMany(Page::class, 'MANAGE', 'accounts', Edge::IN, $options);
    }

    /**
     * @return array
     */
    public function getFetchAccountsFields()
    {
        return [
            'access_token',
            'category',
            'perms',
            'name',
            'id',
        ];
    }
}

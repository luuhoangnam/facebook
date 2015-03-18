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
 * @property string          avatar
 *
 */
class User extends Profile
{

    protected $fields = [
        // Public fields
        'id',
        'name',
        'first_name',
        'last_name',
        'link',
        'gender',
        'locale',
        'verified',
        'timezone',
        'updated_time',
        'picture',
        // Restrict fields
    ];

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

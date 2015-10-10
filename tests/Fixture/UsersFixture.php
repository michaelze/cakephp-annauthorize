<?php
namespace AnnAuthorize\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for users table.
 */
class UsersFixture extends TestFixture
{
    const DEFAULT_USER_ID = '10bc34d0-7299-11e4-82f8-0800200c9a66';

    public $fields = [
        'id' => ['type' => 'string', 'length' => 36],
        'username' => ['type' => 'string', 'length' => 128],
        'password' => ['type' => 'string', 'length' => 60],
        'email' => ['type' => 'string', 'length' => 128],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ]
    ];

    public $records = [
        [
            'id' => 'superadmin',
            'username' => 'superadmin',
            'password' => '',
            'email' => 'superadmin@example.com',
        ],
        [
            'id' => self::DEFAULT_USER_ID,
            'username' => 'tester',
            'password' => '',
            'email' => 'tester@example.com',
        ]
    ];
}
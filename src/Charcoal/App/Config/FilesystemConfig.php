<?php

namespace Charcoal\App\Config;

// Module `charcoal-config` dependencies
use Charcoal\Config\AbstractConfig;

/**
 *
 */
class FilesystemConfig extends AbstractConfig
{

    /**
     * @var array
     */
    public $connections;

    /**
     * @var string
     */
    public $defaultConnection;

    /**
     * @return array
     */
    public function defaults()
    {
        $defaults = [
            'connections' => [
                'private' => [
                    'type' => 'local',
                    'path' => '/'
                ],
                'public' => [
                    'type' => 'local',
                    'path' => 'www/'
                ]
            ],
            'default_connection' => 'public'
        ];
        return array_merge(parent::defaults(), $defaults);
    }
}
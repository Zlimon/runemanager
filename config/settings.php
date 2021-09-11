<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | In here you can define all the settings used in your app, it will be
    | available as a settings page where user can update it if needed
    | create sections of settings with a type of input.
    */

    'site' => [
        'title' => 'Site',
        'desc' => 'General settings for the site',
        'icon' => 'fas fa-columns',

        'elements' => [
            [
                'type' => 'text',
                'data' => 'string',
                'key' => 'site_name',
                'label' => 'Site Name',
                'rules' => 'required|min:2|max:50',
                'description' => 'Should be the name of the clan in-game this site is served for.',
                'class' => '',
                'value' => env("APP_NAME", "RuneManager"),
            ],
            [
                'type' => 'select',
                'data' => 'boolean',
                'key' => 'resource_pack_id',
                'label' => 'Resource pack',
                'rules' => 'required|boolean',
                'description' => 'Select which resource pack to use on the site.',
                'class' => '',
                'value' => '0',
                'options' => [
                    '0' => 'pack 1',
                    '1' => 'pack 2'
                ]
            ],
            [
                'type' => 'number',
                'data' => 'int',
                'key' => 'skill_total_level_requirement',
                'label' => 'Skill total level requirement',
                'description' => 'Minimum required skill total level to link an account on the site.',
                'rules' => 'required|numeric|min:32|max:2277',
                'class' => 'w-auto px-2',
                'value' => '1000',
            ],
            [
                'type' => 'number',
                'data' => 'int',
                'key' => 'combat_level_requirement',
                'label' => 'Combat level requirement',
                'description' => 'Minimum required combat level to link an account on the site.',
                'rules' => 'required|numeric|min:3|max:126',
                'class' => 'w-auto px-2',
                'value' => '50',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'allow_new_users',
                'label' => 'Allow new users',
                'description' => 'Allow visitors to register a user. The user will then be able to link an account.',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'allow_new_accounts',
                'label' => 'Allow new accounts',
                'description' => 'Allow registered users to authenticate an Old School RuneScape account, and submit data from the game via the plugin.',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'number',
                'data' => 'int',
                'key' => 'maximum_linked_accounts_per_user',
                'label' => 'Maximum accounts allowed',
                'description' => 'Maximum number of accounts linked per user allowed.',
                'rules' => 'required|numeric|min:1',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],

        ]
    ],
    'flows' => [
        'title' => 'Flows',
        'desc' => 'Configure which flows are enabled between the game and the site',
        'icon' => 'fas fa-exchange-alt',

        'elements' => [
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_log_in',
                'label' => 'Enable log in requests',
                'description' => '',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_log_out',
                'label' => 'Enable log out requests',
                'description' => '',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_collection_log',
                'label' => 'Enable Collection Log requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>Full Collection Log synchronization</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_loot',
                'label' => 'Enable Loot requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>New Collection Log entries</li>
                        <li>Unique drops</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_skills',
                'label' => 'Enable Skills requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>Level ups</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_quests',
                'label' => 'Enable Quests requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>Full Quest list synchronization</li>
                        <li>Quest completion</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_equipment',
                'label' => 'Enable Equipment requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>Full Equipment synchronization</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
            [
                'type' => 'checkbox',
                'data' => 'boolean',
                'key' => 'enable_bank',
                'label' => 'Enable Bank requests',
                'description' => '
                    This enables:
                    <ul>
                        <li>Full Bank synchronization</li>
                        <li>Bank changes</li>
                    </ul>
                ',
                'rules' => 'required|boolean',
                'class' => 'w-auto px-2',
                'value' => '1',
            ],
        ]
    ],
];

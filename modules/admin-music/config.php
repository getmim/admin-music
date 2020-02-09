<?php

return [
    '__name' => 'admin-music',
    '__version' => '0.0.2',
    '__git' => 'git@github.com:getmim/admin-music.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/admin-music' => ['install','update','remove'],
        'theme/admin/music' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'admin' => NULL
            ],
            [
                'music' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-pagination' => NULL
            ],
            [
                'admin-site-meta' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'AdminMusic\\Controller' => [
                'type' => 'file',
                'base' => 'modules/admin-music/controller'
            ],
            'AdminMusic\\Library' => [
                'type' => 'file',
                'base' => 'modules/admin-music/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'admin' => [
            'adminMusic' => [
                'path' => [
                    'value' => '/music/item'
                ],
                'method' => 'GET',
                'handler' => 'AdminMusic\\Controller\\Music::index'
            ],
            'adminMusicEdit' => [
                'path' => [
                    'value' => '/music/item/(:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminMusic\\Controller\\Music::edit'
            ],
            'adminMusicRemove' => [
                'path' => [
                    'value' => '/music/item/(:id)/remove',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminMusic\\Controller\\Music::remove'
            ],
            'adminMusicAlbum' => [
                'path' => [
                    'value' => '/music/album'
                ],
                'method' => 'GET',
                'handler' => 'AdminMusic\\Controller\\Album::index'
            ],
            'adminMusicAlbumEdit' => [
                'path' => [
                    'value' => '/music/album/(:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminMusic\\Controller\\Album::edit'
            ],
            'adminMusicAlbumRemove' => [
                'path' => [
                    'value' => '/music/album/(:id)/remove',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminMusic\\Controller\\Album::remove'
            ]
        ]
    ],
    'adminUi' => [
        'sidebarMenu' => [
            'items' => [
                'music' => [
                    'label' => 'Music',
                    'icon' => '<i class="fas fa-music"></i>',
                    'priority' => 0,
                    'children' => [
                        'all-music' => [
                            'label' => 'All Music',
                            'icon' => '<i></i>',
                            'route' => ['adminMusic'],
                            'perms' => 'manage_music',
                            'priority' => 1
                        ],
                        'album' => [
                            'label' => 'Album',
                            'icon' => '<i></i>',
                            'route' => ['adminMusicAlbum'],
                            'perms' => 'manage_music_album',
                            'priority' => 0
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'admin.music.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => TRUE,
                    'rules' => []
                ],
                'album' => [
                    'label' => 'Album',
                    'type' => 'text',
                    'nolabel' => TRUE,
                    'rules' => []
                ]
            ],
            'admin.music.edit' => [
                '@extends' => ['std-site-meta'],
                'album' => [
                    'label' => 'Album',
                    'type' => 'select',
                    'sl-filter' => [
                        'route' => 'adminObjectFilter',
                        'params' => [],
                        'query' => [
                            'type' => 'music-album'
                        ]
                    ],
                    'rules' => [
                        'exists' => [
                            'model' => 'Music\\Model\\MusicAlbum',
                            'field' => 'id'
                        ]
                    ]
                ],
                'title' => [
                    'label' => 'Title',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ],
                'slug' => [
                    'label' => 'Slug',
                    'type' => 'text',
                    'slugof' => 'title',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE,
                        'unique' => [
                            'model' => 'Music\\Model\\Music',
                            'field' => 'slug',
                            'self' => [
                                'service' => 'req.param.id',
                                'field' => 'id'
                            ]
                        ]
                    ]
                ],
                'file' => [
                    'label' => 'Audio File',
                    'type' => 'file',
                    'rules' => [
                        'required' => TRUE,
                        'upload' => 'std-audio'
                    ]
                ],
                'content' => [
                    'label' => 'About',
                    'type' => 'summernote',
                    'rules' => []
                ],
                'meta-schema' => [
                    'options' => [
                        'MusicRecording' => 'MusicRecording'
                    ]
                ]
            ],
            'admin.music-album.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => TRUE,
                    'rules' => []
                ]
            ],
            'admin.music-album.edit' => [
                '@extends' => ['std-site-meta'],
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ],
                'slug' => [
                    'label' => 'Slug',
                    'type' => 'text',
                    'slugof' => 'name',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE,
                        'unique' => [
                            'model' => 'Music\\Model\\MusicAlbum',
                            'field' => 'slug',
                            'self' => [
                                'service' => 'req.param.id',
                                'field' => 'id'
                            ]
                        ]
                    ]
                ],
                'cover' => [
                    'label' => 'Cover',
                    'type' => 'image',
                    'form' => 'std-image',
                    'rules' => [
                        'required' => TRUE,
                        'upload' => TRUE
                    ]
                ],
                'author' => [
                    'label' => 'Author',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ],
                'content' => [
                    'label' => 'About',
                    'type' => 'summernote',
                    'rules' => []
                ],
                'release' => [
                    'label' => 'Release Date',
                    'type' => 'date',
                    'rules' => []
                ],
                'meta-schema' => [
                    'options' => [
                        'MusicAlbum'=>'MusicAlbum'
                    ]
                ]
            ]
        ]
    ],
    'admin' => [
        'objectFilter' => [
            'handlers' => [
                'music-album' => 'AdminMusic\\Library\\Filter'
            ]
        ]
    ]
];

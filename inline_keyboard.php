<?php

/*
 * All function that create inline_keyboard are declared here
 */

function &getAddInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Add_Button', $language),
                    'callback_data' => 'add'
                ],
                [
                    'text' => getMessage('Options_Button', $language),
                    'callback_data' => 'options/menu'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function &getMenuInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Add_Button', $language),
                    'callback_data' => 'add'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('BrowseAB_Button', $language),
            'callback_data' => 'show/ab'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Options_Button', $language),
            'callback_data' => 'options/menu'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getChooseLanguageStartInlineKeyboard() {
    $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => '🇬🇧 English',
                        'callback_data' => 'cls/en'
                    ]
                ]
            ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => '🇮🇹 Italiano',
            'callback_data' => 'cls/it'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
        'text' => '🇩🇪 Deutsch',
        'callback_data' => 'cls/de'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => 'Русский',
            'callback_data' => 'cls/ru'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => '🇮🇳 हिन्दी',
            'callback_data' => 'cls/hi'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getBackInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Back_Button', $language),
                    'callback_data' => 'back'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function &getBackSkipInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Back_Button', $language),
                    'callback_data' => 'back'
                ],
                [
                    'text' => getMessage('Skip_Button', $language),
                    'callback_data' => 'skip'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function getBackSearchInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Back_Button', $language),
                    'callback_data' => 'show/ab'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function &getBackDeleteInlineKeyboard($islastname, &$language) {
    if ($islastname) {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => getMessage('Back_Button', $language),
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => getMessage('DeleteLastName_Button', $language),
                        'callback_data' => 'delete/info'
                    ]
                ]
            ]
        ];
    } else {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => getMessage('Back_Button', $language),
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => getMessage('DeleteDescription_Button', $language),
                        'callback_data' => 'delete/info'
                    ]
                ]
            ]
        ];
    }
    return json_encode($inline_keyboard);
}

function &getOptionsInlineKeyboard(&$language, $from) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Language_Button', $language),
                    'callback_data' => 'language'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('UpdateAll_Button', $language),
            'callback_data' => 'update/all'
        ],
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Order_Button', $language),
            'callback_data' => 'order'
        ],
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('DeleteAll_Button', $language),
            'callback_data' => 'delete/allprompt'
        ],
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Back_Button', $language),
            'callback_data' => $from
        ],
    ]);
    return json_encode($inline_keyboard);
}

function getOrderInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('FirstOrder_Button', $language),
                    'callback_data' => 'order/0'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('SecondOrder_Button', $language),
            'callback_data' => 'order/1'
        ],
     ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('ThirdOrder_Button', $language),
            'callback_data' => 'order/2'
        ],
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Back_Button', $language),
            'callback_data' => 'back'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getChooseLanguageInlineKeyboard(&$language) {
    $inline_keyboard = ['inline_keyboard' => array()];
    foreach($GLOBALS['messages']['launguages'] as $languages => $language_msg) {
        if ($language_msg === getMessage('Language', $language)) {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => $language_msg,
                    'callback_data' => 'same/language'
                ]
            ]);
        } else {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => $language_msg . '/' . getMessage($languages, $language),
                    'callback_data' => 'cl/' . $languages
                ]
            ]);
        }
    }
    unset($languages);
    unset($language_msg);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Back_Button', $language),
            'callback_data' => 'back'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getEditASInlineKeyboard(&$chat_id, &$row, &$language, REDIS &$redis) {
    if (isset($row['id_contact']) && $row['id_contact'] !== 'NULL') {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => getMessage('UpdateUsername_Button', $language),
                        'callback_data' => 'update/username'
                    ],
                    [
                        'text' => getMessage('EditFirstName_Button', $language),
                        'callback_data' => 'edit/firstname'
                    ]
                ]
            ]
        ];
    } else {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => getMessage('EditUsername_Button', $language),
                        'callback_data' => 'edit/username'
                    ],
                    [
                        'text' => getMessage('EditFirstName_Button', $language),
                        'callback_data' => 'edit/firstname'
                    ]
                ]
            ]
        ];
    }
    if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
        if (isset($row['desc'])  && ($row['desc'] !== 'NULL')) {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => getMessage('EditLastName_Button', $language),
                    'callback_data' => 'edit/lastname'
                ],
                [
                    'text' => getMessage('EditDescription_Button', $language),
                    'callback_data' => 'edit/desc'
                ]
            ]);
        } else {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => getMessage('EditLastName_Button', $language),
                    'callback_data' => 'edit/lastname'
                ],
                [
                    'text' => getMessage('AddDescription_Button', $language),
                    'callback_data' => 'add/desc'
                ]
            ]);
        }
    } elseif (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => getMessage('AddLastName_Button', $language),
                'callback_data' => 'add/lastname'
            ],
            [
                'text' => getMessage('EditDescription_Button', $language),
                'callback_data' => 'edit/desc'
            ]
        ]);
    } else {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => getMessage('AddLastName_Button', $language),
                'callback_data' => 'add/lastname'
            ],
            [
                'text' => getMessage('AddDescription_Button', $language),
                'callback_data' => 'add/desc'
            ]
        ]);
    }
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Share_Button', $language),
            'switch_inline_query' => $row['username']
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Delete_Button', $language),
            'callback_data' => 'delete/asprompt'
        ]
    ]);
    if ($redis->exists($chat_id . ':search_query')) {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => getMessage('BackToSearch_Button', $language),
                'callback_data' => 'back/search'
            ]
        ]);
    }
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('BrowseAB_Button', $language),
            'callback_data' => 'show/ab'
        ],
        [
            'text' => getMessage('Menu_Button', $language),
            'callback_data' => 'menu'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getDeleteASPromptInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Cancel_Button', $language),
                    'callback_data' => 'back'
                ],
                [
                    'text' => getMessage('Confirm_Button', $language),
                    'callback_data' => 'delete/as'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function &getDeleteAllPromptInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Cancel_Button', $language),
                    'callback_data' => 'back'
                ],
                [
                    'text' => getMessage('Confirm_Button', $language),
                    'callback_data' => 'delete/all'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}


function &getCancelSkipInlineKeyBoard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Cancel_Button', $language),
                    'callback_data' => 'back'
                ],
                [
                    'text' => getMessage('Skip_Button', $language),
                    'callback_data' => 'skip'
                ]
            ]
        ]
    ];
    return json_encode($inline_keyboard);
}

function &getSaveInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Save_Button', $language),
                    'callback_data' => 'save',
                ],
                [
                    'text' => getMessage('Edit_Button', $language),
                    'callback_data' => 'edit/contact',
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('AddDescription_Button', $language),
            'callback_data' => 'add/desc&save',
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Cancel_Button', $language),
            'callback_data' => 'back',
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getSaveFromInlineInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('SaveFromInline_Button', $language),
                    'callback_data' => 'null',
                ]
            ]
        ]
    ];
    return $inline_keyboard;
}

function &getShareInlineKeyboard(&$share, &$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('SaveFromInline_Button', $language),
                    'callback_data' => 'shared/' . $share,
                ]
            ]
        ]
    ];
    return $inline_keyboard;
}

// Show basic button when a non valid function or data is requested
function &getASNotValidInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Add_Button', $language),
                    'callback_data' => 'add'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('BrowseAB_Button', $language),
            'callback_data' => 'show/ab'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Menu_Button', $language),
            'callback_data' => 'menu'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getABEmptyInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('Add_Button', $language),
                    'callback_data' => 'add'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Menu_Button', $language),
            'callback_data' => 'menu'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Options_Button', $language),
            'callback_data' => 'options/ab'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getSearchNullInlineKeyboard(&$language) {
    $inline_keyboard = [ 'inline_keyboard' =>
        [
            [
                [
                    'text' => getMessage('SearchAgain_Button', $language),
                    'callback_data' => 'search'
                ]
            ]
        ]
    ];
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('BrowseAB_Button', $language),
            'callback_data' => 'show/ab'
        ]
    ]);
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Menu_Button', $language),
            'callback_data' => 'menu'
        ]
    ]);
    return json_encode($inline_keyboard);
}

function &getListInlineKeyboard(&$index, &$list, &$usernames, &$language, $prefix = 'ab') {
    if ($list > 0) {
        if ($index == 1) {
            if ($list > 1) {
                if ($list > 2) {
                    if ($list > 3) {
                        if ($list > 4) {
                            if ($list > 5) {
                                $inline_keyboard = [ 'inline_keyboard' =>
                                    [
                                        [
                                            [
                                                'text' => '• 1 •',
                                                'callback_data' => 'null'
                                            ],
                                            [
                                                'text' => '2',
                                                'callback_data' => $prefix . "/2"
                                            ],
                                            [
                                                'text' => '3',
                                                'callback_data' => $prefix . "/3"
                                            ],
                                            [
                                                'text' => '4>',
                                                'callback_data' => $prefix . "/4"
                                            ],
                                            [
                                                'text' => "$list>>",
                                                'callback_data' => $prefix . "/$list"
                                            ]
                                            ]
                                        ]
                                ];
                            } else {
                                $inline_keyboard = [ 'inline_keyboard' =>
                                    [
                                        [
                                            [
                                                'text' => '• 1 •',
                                                'callback_data' => 'null'
                                            ],
                                            [
                                                'text' => '2',
                                                'callback_data' => $prefix . "/2"
                                            ],
                                            [
                                                'text' => '3',
                                                'callback_data' => $prefix . "/3"
                                            ],
                                            [
                                                'text' => '4',
                                                'callback_data' => $prefix . "/4"
                                            ],
                                            [
                                                'text' => '5',
                                                'callback_data' => $prefix . "/5"
                                            ]
                                        ]
                                    ]
                                ];
                            }
                        } else {
                            $inline_keyboard = [ 'inline_keyboard' =>
                                [
                                    [
                                        [
                                            'text' => '• 1 •',
                                            'callback_data' => 'null'
                                        ],
                                        [
                                            'text' => '2',
                                            'callback_data' => $prefix . "/2"
                                        ],
                                        [
                                            'text' => '3',
                                            'callback_data' => $prefix . "/3"
                                        ],
                                        [
                                            'text' => '4',
                                            'callback_data' => $prefix . "/4"
                                        ],
                                    ]
                                ]
                            ];
                        }
                    } else {
                        $inline_keyboard = [ 'inline_keyboard' =>
                            [
                                [
                                    [
                                        'text' => '• 1 •',
                                        'callback_data' => 'null'
                                    ],
                                    [
                                        'text' => '2',
                                        'callback_data' => $prefix . "/2"
                                    ],
                                    [
                                        'text' => '3',
                                        'callback_data' => $prefix . "/3"
                                    ],
                                ]
                            ]
                        ];
                    }
                } elseif ($list == 2) {
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '• 1 •',
                                    'callback_data' => 'null'
                                ],
                                [
                                    'text' => '2',
                                    'callback_data' => $prefix . "/2"
                                ],
                            ]
                        ]
                    ];
                }
            } else {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '• 1 •',
                                'callback_data' => 'null'
                            ]
                        ]
                    ]
                ];
            }
        } elseif ($index == 2) {
            if ($list > 3) {
                if ($list > 4) {
                    if ($list > 5) {
                        $inline_keyboard = [ 'inline_keyboard' =>
                            [
                                [
                                    [
                                        'text' => '1',
                                        'callback_data' => $prefix . "/1"
                                    ],
                                    [
                                        'text' => '• 2 •',
                                        'callback_data' => 'null'
                                    ],
                                    [
                                        'text' => '3',
                                        'callback_data' => $prefix . "/3"
                                    ],
                                    [
                                        'text' => '4>',
                                        'callback_data' => $prefix . "/4"
                                    ],
                                    [
                                        'text' => "$list>>",
                                        'callback_data' => $prefix . "/$list"
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        $inline_keyboard = [ 'inline_keyboard' =>
                            [
                                [
                                    [
                                        'text' => '1',
                                        'callback_data' => $prefix . "/1"
                                    ],
                                    [
                                        'text' => '• 2 •',
                                        'callback_data' => 'null'
                                    ],
                                    [
                                        'text' => '3',
                                        'callback_data' => $prefix . "/3"
                                    ],
                                    [
                                        'text' => '4',
                                        'callback_data' => '4'
                                    ],
                                    [
                                        'text' => '5',
                                        'callback_data' => $prefix . "/5"
                                    ]
                                ]
                            ]
                        ];
                    }
                } else {
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '1',
                                    'callback_data' => $prefix . "/1"
                                ],
                                [
                                    'text' => '• 2 •',
                                    'callback_data' => 'null'
                                ],
                                [
                                    'text' => '3',
                                    'callback_data' => $prefix . "/3"
                                ],
                                [
                                    'text' => '4',
                                    'callback_data' => $prefix . "/4"
                                ],
                            ]
                        ]
                    ];
                }
            } elseif ($list == 3) {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '• 2 •',
                                'callback_data' => 'null'
                            ],
                            [
                                'text' => '3',
                                'callback_data' => $prefix . "/3"
                            ],
                        ]
                    ]
                ];
            } else {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '• 2 •',
                                'callback_data' => 'null'
                            ]
                        ]
                    ]
                ];
            }
        } elseif ($index == 3) {
            if ($list > 4) {
                if ($list > 5) {
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '1',
                                    'callback_data' => $prefix . "/1"
                                ],
                                [
                                    'text' => '2',
                                    'callback_data' => $prefix . "/2"
                                ],
                                [
                                    'text' => '• 3 •',
                                    'callback_data' => 'null'
                                ],
                                [
                                    'text' => '4>',
                                    'callback_data' => $prefix . "/4"
                                ],
                                [
                                    'text' => "$list>>",
                                    'callback_data' => $prefix . "/$list"
                                ],
                            ]
                        ]
                    ];
                } else {
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '1',
                                    'callback_data' => $prefix . "/1"
                                ],
                                [
                                    'text' => '2',
                                    'callback_data' => $prefix . "/2"
                                ],
                                [
                                    'text' => '• 3 •',
                                    'callback_data' => 'null'
                                ],
                                [
                                    'text' => '4',
                                    'callback_data' => $prefix . "/4"
                                ],
                                [
                                    'text' => '5',
                                    'callback_data' => $prefix . "/5"
                                ]
                            ]
                        ]
                    ];
                }
            } elseif ($list == 4) {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '2',
                                'callback_data' => $prefix . "/2"
                            ],
                            [
                                'text' => '• 3 •',
                                'callback_data' => 'null'
                            ],
                            [
                                'text' => '4',
                                'callback_data' => $prefix . "/4"
                            ],
                        ]
                    ]
                ];
            } else {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '2',
                                'callback_data' => $prefix . "/2"
                            ],
                            [
                                'text' => '• 3 •',
                                'callback_data' => 'null'
                            ]
                        ]
                    ]
                ];
            }
        } elseif ($index == 4 && $list <= 5) {
            if ($list == 4) {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '2',
                                'callback_data' => $prefix . "/2"
                            ],
                            [
                                'text' => '3',
                                'callback_data' => $prefix . "/3"
                            ],
                            [
                                'text' => '• 4 •',
                                'callback_data' => 'null'
                            ]
                        ]
                    ]
                ];
            } else if ($list == 5) {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => '2',
                                'callback_data' => $prefix . "/2"
                            ],
                            [
                                'text' => '3',
                                'callback_data' => $prefix . "/3"
                            ],
                            [
                                'text' => '• 4 •',
                                'callback_data' => 'null'
                            ],
                            [
                                'text' => '5',
                                'callback_data' => $prefix . "/5"
                            ],
                        ]
                    ]
                ];
            }
        } else if ($index == 5 && $list == 5) {
            $inline_keyboard = [ 'inline_keyboard' =>
                [
                    [
                        [
                            'text' => '1',
                            'callback_data' => $prefix . "/1"
                        ],
                        [
                            'text' => '2',
                            'callback_data' => $prefix . "/2"
                        ],
                        [
                            'text' => '3',
                            'callback_data' => $prefix . "/3"
                        ],
                        [
                            'text' => '4',
                            'callback_data' => $prefix . "/4"
                        ],
                        [
                            'text' => '• 5 •',
                            'callback_data' => 'null'
                        ],
                    ]
                ]
            ];
        } else {
            if ($index < $list - 2) {
                $indexm = $index - 1;
                $indexp = $index + 1;
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '<<1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => "<$indexm",
                                'callback_data' => $prefix . "/$indexm"
                            ],
                            [
                                'text' => "• $index •",
                                'callback_data' => 'null',
                            ],
                            [
                                'text' => "$indexp>",
                                'callback_data' => $prefix . "/$indexp"
                            ],
                            [
                                'text' => "$list>>",
                                'callback_data' => $prefix . "/$list"
                            ]
                        ]
                    ]
                ];
            } elseif ($index == ($list - 2)) {
                $indexm = $index - 1;
                $indexp = $index + 1;
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '<<1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => "$indexm",
                                'callback_data' => $prefix . "/$indexm"
                            ],
                            [
                                'text' => "• $index •",
                                'callback_data' => 'null',
                            ],
                            [
                                'text' => "$indexp",
                                'callback_data' => $prefix . "/$indexp"
                            ],
                            [
                            'text' => "$list",
                            'callback_data' => $prefix . "/$list"
                            ]
                        ]
                    ]
                ];
            } elseif ($index == ($list - 1)) {
                $indexm = $index - 1;
                $indexmm = $index - 2;
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '<<1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => "<$indexmm",
                                'callback_data' => $prefix . "/$indexmm"
                            ],
                            [
                                'text' => "$indexm",
                                'callback_data' => $prefix . "/$indexm",
                            ],
                            [
                                'text' => "• $index •",
                                'callback_data' => $prefix . "/$index"
                            ],
                            [
                                'text' => "$list",
                                'callback_data' => $prefix . "/$list"
                            ]
                        ]
                    ]
                ];
            } else if ($index == $list) {
                $indexm = $index - 1;
                $indexmm = $index - 2;
                $indexmmm = $index - 3;
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '<<1',
                                'callback_data' => $prefix . "/1"
                            ],
                            [
                                'text' => "<$indexmmm",
                                'callback_data' => $prefix . "/$indexmmm"
                            ],
                            [
                                'text' => "$indexmm",
                                'callback_data' => $prefix . "/$indexmm",
                            ],
                            [
                                'text' => "$indexm",
                                'callback_data' => $prefix . "/$indexm"
                            ],
                            [
                                'text' => "• $index •",
                                'callback_data' => $prefix . "/$index"
                            ]
                        ]
                    ]
                ];
            }
        }
    }
    if (isset($usernames[2]['text'])) {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => $usernames[0]['text'],
                'callback_data' => $usernames[0]['callback_data'] . '/' . $prefix
            ],
            [
                'text' => $usernames[1]['text'],
                'callback_data' => $usernames[1]['callback_data'] . '/' . $prefix
            ],
            [
                'text' => $usernames[2]['text'],
                'callback_data' => $usernames[2]['callback_data'] . '/' . $prefix
            ]
        ]);
    } else if (isset($usernames[1]['text'])) {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => $usernames[0]['text'],
                'callback_data' => $usernames[0]['callback_data'] . '/' . $prefix
            ],
            [
                'text' => $usernames[1]['text'],
                'callback_data' => $usernames[1]['callback_data'] . '/' . $prefix
            ],
        ]);
    } else {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => $usernames[0]['text'],
                'callback_data' => $usernames[0]['callback_data'] . '/' . $prefix
            ],
        ]);
    }
    if ($prefix == 'ab')
    {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => getMessage('Add_Button', $language),
                'callback_data' => 'add'
            ],
            [
                'text' => getMessage('Search_Button', $language),
                'callback_data' => 'search'
            ]
        ]);
    } else {
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => getMessage('BrowseAB_Button', $language),
                'callback_data' => 'show/ab'
            ],
            [
                'text' => getMessage('NewSearch_Button', $language),
                'callback_data' => 'search'
            ]
        ]);
    }
    array_push($inline_keyboard['inline_keyboard'], [
        [
            'text' => getMessage('Menu_Button', $language),
            'callback_data' => 'menu'
        ],
        [
            'text' => getMessage('Options_Button', $language),
            'callback_data' => 'options/ab'
        ]
    ]);
    return json_encode($inline_keyboard);
}

<?php

class InlineKeyboard extends PhpBotFramework\Localization\Button {
    public function getAddInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Add_Button'],
                        'callback_data' => 'add'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Options_Button'],
                        'callback_data' => 'options/menu'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getMenuInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Add_Button'],
                        'callback_data' => 'add'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['BrowseAB_Button'],
                        'callback_data' => 'show/ab'
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Help_Button'],
                'callback_data' => 'help'
            ],
            [
                'text' => &$this->bot->localization[$this->bot->language]['About_Button'],
                'callback_data' => 'about'
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Options_Button'],
                'callback_data' => 'options/menu'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getChooseLanguageStartInlineKeyboard() {
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

    public function getBackInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                        'callback_data' => 'back'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getBackSkipInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Skip_Button'],
                        'callback_data' => 'skip'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getBackSearchInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                        'callback_data' => 'show/ab'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getBackDeleteInlineKeyboard($islastname) {
        if ($islastname) {
            $inline_keyboard = [ 'inline_keyboard' =>
                [
                    [
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                            'callback_data' => 'back'
                        ],
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['DeleteLastName_Button'],
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
                            'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                            'callback_data' => 'back'
                        ],
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['DeleteDescription_Button'],
                            'callback_data' => 'delete/info'
                        ]
                    ]
                ]
            ];
        }
        return json_encode($inline_keyboard);
    }

    public function getOptionsInlineKeyboard(&$from) {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Language_Button'],
                        'callback_data' => 'language'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['UpdateAll_Button'],
                        'callback_data' => 'update/all'
                    ],
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Order_Button'],
                'callback_data' => 'order'
            ],
            [
                'text' => &$this->bot->localization[$this->bot->language]['DeleteAll_Button'],
                'callback_data' => 'delete/allprompt'
            ],
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                'callback_data' => &$from
            ],
        ]);
        return json_encode($inline_keyboard);
    }

    public function getOrderInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['FirstOrder_Button'],
                        'callback_data' => 'order/0'
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['SecondOrder_Button'],
                'callback_data' => 'order/1'
            ],
         ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['ThirdOrder_Button'],
                'callback_data' => 'order/2'
            ],
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                'callback_data' => 'back'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getChooseLanguageInlineKeyboard() {
        $inline_keyboard = ['inline_keyboard' => array()];
        foreach($this->bot->localization['launguages'] as $languages => $language_msg) {
            if (strpos($languages, $this->bot->language) !== false) {
                array_push($inline_keyboard['inline_keyboard'], [
                    [
                        'text' => $language_msg,
                        'callback_data' => 'same/language'
                    ]
                ]);
            } else {
                array_push($inline_keyboard['inline_keyboard'], [
                    [
                        'text' => $language_msg . '/' . $this->bot->localization[$this->bot->language][$languages],
                        'callback_data' => 'cl/' . $languages
                    ]
                ]);
            }
        }
        unset($languages);
        unset($language_msg);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Back_Button'],
                'callback_data' => 'back'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getEditContactInlineKeyboard(&$row) {
        if (isset($row['id_contact']) && $row['id_contact'] !== 'NULL') {
            $inline_keyboard = [ 'inline_keyboard' =>
                [
                    [
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['UpdateUsername_Button'],
                            'callback_data' => 'update/username'
                        ],
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['EditFirstName_Button'],
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
                            'text' => &$this->bot->localization[$this->bot->language]['EditUsername_Button'],
                            'callback_data' => 'edit/username'
                        ],
                        [
                            'text' => &$this->bot->localization[$this->bot->language]['EditFirstName_Button'],
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
                        'text' => &$this->bot->localization[$this->bot->language]['EditLastName_Button'],
                        'callback_data' => 'edit/lastname'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['EditDescription_Button'],
                        'callback_data' => 'edit/desc'
                    ]
                ]);
            } else {
                array_push($inline_keyboard['inline_keyboard'], [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['EditLastName_Button'],
                        'callback_data' => 'edit/lastname'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['AddDescription_Button'],
                        'callback_data' => 'add/desc'
                    ]
                ]);
            }
        } elseif (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => &$this->bot->localization[$this->bot->language]['AddLastName_Button'],
                    'callback_data' => 'add/lastname'
                ],
                [
                    'text' => &$this->bot->localization[$this->bot->language]['EditDescription_Button'],
                    'callback_data' => 'edit/desc'
                ]
            ]);
        } else {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => &$this->bot->localization[$this->bot->language]['AddLastName_Button'],
                    'callback_data' => 'add/lastname'
                ],
                [
                    'text' => &$this->bot->localization[$this->bot->language]['AddDescription_Button'],
                    'callback_data' => 'add/desc'
                ]
            ]);
        }
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Share_Button'],
                'switch_inline_query' => $row['username']
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Delete_Button'],
                'callback_data' => 'delete/asprompt'
            ]
        ]);
        if ($this->bot->redis->exists($this->bot->getChatID() . ':search_query')) {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => &$this->bot->localization[$this->bot->language]['BackToSearch_Button'],
                    'callback_data' => 'back/search'
                ]
            ]);
        }
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['BrowseAB_Button'],
                'callback_data' => 'show/ab'
            ],
            [
                'text' => &$this->bot->localization[$this->bot->language]['Menu_Button'],
                'callback_data' => 'menu'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getDeleteContactPromptInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Cancel_Button'],
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Confirm_Button'],
                        'callback_data' => 'delete/as'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getDeleteAllPromptInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Cancel_Button'],
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Confirm_Button'],
                        'callback_data' => 'delete/all'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }


    public function getCancelSkipInlineKeyBoard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Cancel_Button'],
                        'callback_data' => 'back'
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Skip_Button'],
                        'callback_data' => 'skip'
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    public function getSaveInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Save_Button'],
                        'callback_data' => 'save',
                    ],
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Edit_Button'],
                        'callback_data' => 'edit/contact',
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['AddDescription_Button'],
                'callback_data' => 'add/desc&save',
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Cancel_Button'],
                'callback_data' => 'back',
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getSaveFromInlineInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => $this->bot->localization[$this->bot->language]['SaveFromInline_Button'],
                        'callback_data' => 'null',
                    ]
                ]
            ]
        ];
        return $inline_keyboard;
    }

    public function getShareInlineKeyboard(&$share) {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => "hfhau", //&$this->bot->localization[$this->bot->language]['SaveFromInline_Button'],
                        'callback_data' => 'shared/' . $share,
                    ]
                ]
            ]
        ];
        return json_encode($inline_keyboard);
    }

    // Show basic button when a non valid public function or data is requested
    public function getContactNotValidInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Add_Button'],
                        'callback_data' => 'add'
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['BrowseAB_Button'],
                'callback_data' => 'show/ab'
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Menu_Button'],
                'callback_data' => 'menu'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getABEmptyInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['Add_Button'],
                        'callback_data' => 'add'
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Menu_Button'],
                'callback_data' => 'menu'
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Options_Button'],
                'callback_data' => 'options/ab'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

    public function getSearchNullInlineKeyboard() {
        $inline_keyboard = [ 'inline_keyboard' =>
            [
                [
                    [
                        'text' => &$this->bot->localization[$this->bot->language]['SearchAgain_Button'],
                        'callback_data' => 'search'
                    ]
                ]
            ]
        ];
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['BrowseAB_Button'],
                'callback_data' => 'show/ab'
            ]
        ]);
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => &$this->bot->localization[$this->bot->language]['Menu_Button'],
                'callback_data' => 'menu'
            ]
        ]);
        return json_encode($inline_keyboard);
    }



    public function getListInlineKeyboard(&$list, &$usernames, $prefix = 'ab') {
        if ($list > 0) {
            if ($this->bot->index_addressbook == 1) {
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
                                                    'text' => '4 ›',
                                                    'callback_data' => $prefix . "/4"
                                                ],
                                                [
                                                    'text' => "$list ››",
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
            } elseif ($this->bot->index_addressbook == 2) {
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
                                            'text' => '4 ›',
                                            'callback_data' => $prefix . "/4"
                                        ],
                                        [
                                            'text' => "$list ››",
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
            } elseif ($this->bot->index_addressbook == 3) {
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
                                        'text' => '4 ›',
                                        'callback_data' => $prefix . "/4"
                                    ],
                                    [
                                        'text' => "$list ››",
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
            } elseif ($this->bot->index_addressbook == 4 && $list <= 5) {
                if ($list == 4) {
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '1',
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '2',
                                    'callback_data' => $prefix . '/2'
                                ],
                                [
                                    'text' => '3',
                                    'callback_data' => $prefix . '/3'
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
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '2',
                                    'callback_data' => $prefix . '/2'
                                ],
                                [
                                    'text' => '3',
                                    'callback_data' => $prefix . '/3'
                                ],
                                [
                                    'text' => '• 4 •',
                                    'callback_data' => 'null'
                                ],
                                [
                                    'text' => '5',
                                    'callback_data' => $prefix . '/5'
                                ],
                            ]
                        ]
                    ];
                }
            } else if ($this->bot->index_addressbook == 5 && $list == 5) {
                $inline_keyboard = [ 'inline_keyboard' =>
                    [
                        [
                            [
                                'text' => '1',
                                'callback_data' => $prefix . '/1'
                            ],
                            [
                                'text' => '2',
                                'callback_data' => $prefix . '/2'
                            ],
                            [
                                'text' => '3',
                                'callback_data' => $prefix . '/3'
                            ],
                            [
                                'text' => '4',
                                'callback_data' => $prefix . '/4'
                            ],
                            [
                                'text' => '• 5 •',
                                'callback_data' => 'null'
                            ],
                        ]
                    ]
                ];
            } else {
                if ($this->bot->index_addressbook < $list - 2) {
                    $this->bot->index_addressbookm = $this->bot->index_addressbook - 1;
                    $this->bot->index_addressbookp = $this->bot->index_addressbook + 1;
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '‹‹ 1',
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '‹ ' . $this->bot->index_addressbookm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookm
                                ],
                                [
                                    'text' => '• ' . $this->bot->index_addressbook . ' •',
                                    'callback_data' => 'null',
                                ],
                                [
                                    'text' => $this->bot->index_addressbookp . ' ›',
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookp
                                ],
                                [
                                    'text' => $list . ' ››',
                                    'callback_data' => $prefix . '/' . $list
                                ]
                            ]
                        ]
                    ];
                } elseif ($this->bot->index_addressbook == ($list - 2)) {
                    $this->bot->index_addressbookm = $this->bot->index_addressbook - 1;
                    $this->bot->index_addressbookp = $this->bot->index_addressbook + 1;
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '‹‹1',
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '' . $this->bot->index_addressbookm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookm
                                ],
                                [
                                    'text' => '• ' . $this->bot->index_addressbook . ' •',
                                    'callback_data' => 'null',
                                ],
                                [
                                    'text' => '' . $this->bot->index_addressbookp,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookp
                                ],
                                [
                                'text' => "$list",
                                'callback_data' => $prefix . "/$list"
                                ]
                            ]
                        ]
                    ];
                } elseif ($this->bot->index_addressbook == ($list - 1)) {
                    $this->bot->index_addressbookm = $this->bot->index_addressbook - 1;
                    $this->bot->index_addressbookmm = $this->bot->index_addressbook - 2;
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '‹‹ 1',
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '‹ ' . $this->bot->index_addressbookmm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookmm
                                ],
                                [
                                    'text' => '' . $this->bot->index_addressbookm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookm
                                ],
                                [
                                    'text' => '• ' . $this->bot->index_addressbook . ' •',
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbook
                                ],
                                [
                                    'text' => "$list",
                                    'callback_data' => $prefix . "/$list"
                                ]
                            ]
                        ]
                    ];
                } else if ($this->bot->index_addressbook == $list) {
                    $this->bot->index_addressbookm = $this->bot->index_addressbook - 1;
                    $this->bot->index_addressbookmm = $this->bot->index_addressbook - 2;
                    $this->bot->index_addressbookmmm = $this->bot->index_addressbook - 3;
                    $inline_keyboard = [ 'inline_keyboard' =>
                        [
                            [
                                [
                                    'text' => '‹‹ 1',
                                    'callback_data' => $prefix . '/1'
                                ],
                                [
                                    'text' => '‹ ' . $this->bot->index_addressbookmmm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookmmm
                                ],
                                [
                                    'text' => '' . $this->bot->index_addressbookmm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookmm,
                                ],
                                [
                                    'text' => '' . $this->bot->index_addressbookm,
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbookm
                                ],
                                [
                                    'text' => '• ' . $this->bot->index_addressbook . ' •',
                                    'callback_data' => $prefix . '/' . $this->bot->index_addressbook
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
                    'text' => $this->bot->localization[$this->bot->language]['Add_Button'],
                    'callback_data' => 'add'
                ],
                [
                    'text' => $this->bot->localization[$this->bot->language]['Search_Button'],
                    'callback_data' => 'search'
                ]
            ]);
        } else {
            array_push($inline_keyboard['inline_keyboard'], [
                [
                    'text' => $this->bot->localization[$this->bot->language]['BrowseAB_Button'],
                    'callback_data' => 'show/ab'
                ],
                [
                    'text' => $this->bot->localization[$this->bot->language]['NewSearch_Button'],
                    'callback_data' => 'search'
                ]
            ]);
        }
        array_push($inline_keyboard['inline_keyboard'], [
            [
                'text' => $this->bot->localization[$this->bot->language]['Menu_Button'],
                'callback_data' => 'menu'
            ],
            [
                'text' => $this->bot->localization[$this->bot->language]['Options_Button'],
                'callback_data' => 'options/ab'
            ]
        ]);
        return json_encode($inline_keyboard);
    }

}

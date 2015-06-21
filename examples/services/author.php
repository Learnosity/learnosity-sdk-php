<?php

/*
|--------------------------------------------------------------------------
| Data format for the Learnosity Author API
|--------------------------------------------------------------------------
|
| Use this file to create the necessary data used to instantiate the
| Learnosity Author API, you'll need:
|   - service  (string) (mandatory)
|   - security (array)  (mandatory)
|   - secret   (string) (mandatory)
|   - request  (array)  (optional)
|
| Use the example code below as a template or as a guide. The end result
| should be a JSON object to be passed into LearnosityAuthor.init()
|
*/

$service = 'author';
$security = array(
    'consumer_key' => 'yis0TYCu7U9V4o7M',
    'domain'       => 'localhost',
    'timestamp'    => gmdate('Ymd-Hi')
);
$secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$request = array(
    'mode'      => 'item_edit',
    'reference' => 'my-item-reference',
    'config'    => array(
        'item_edit' => array(
            'item' => array(
                'tags' => array(
                    'include_tags_on_edit' => array(
                        array(
                            'type' => 'course',
                            'name' => 'commoncore'
                        )
                    )
                )
            ),
            'widget' => array(
                'delete' => true,
                'edit' => true
            )
        ),
        'question_editor_init_options' => array(
            'ui' => array(
                'public_methods'     => array(),
                'question_tiles'     => false,
                'documentation_link' => false,
                'change_button'      => true,
                'source_button'      => false,
                'fixed_preview'      => true,
                'advanced_group'     => false,
                'search_field'       => false
            )
        )
    ),
    'user' => array(
        'id'        => 'demos-site',
        'firstname' => 'Demos',
        'lastname'  => 'User',
        'email'     => 'demos@learnosity.com'
    )
);

$heading = 'Author API';
$description = '<p>Retrieve content from the Learnosity ItemBank to embed in your own authoring environment.</p>';

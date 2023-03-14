<?php
return [
    'mode' => 'draft',

    'page_cache' => env('ACTINITE_PAGE_CACHE',false),

    'type_cache' => env('ACTINITE_TYPE_CACHING',true),

	/**
	 * Specify the NodeTypes available to the application.
	 * You can disable built-in types here.
	 */

	'types' => [
		\Actinity\Actinite\Core\Types\Folder::class,
		\Actinity\Actinite\Core\Types\NodeList::class,
		\Actinity\Actinite\Core\Types\PostArchive::class,
		\Actinity\Actinite\Core\Types\Url::class,
	],

	/**
	 * Alternatively, set directory locations that should
	 * be automatically scanned for any subclasses of
	 * Actinity\Actinite\Core\Node.
	 */

	'type_locations' => [
		'app/Nodes' => "App\\Nodes",
	],

    'relations' => [
        /*[
            'source' => \App\Nodes\Interview::class,
            'target' => \App\Nodes\Interviewee::class,
            'name' => 'interviewee',
            'field' => 'interviewee_id',
        ]*/
    ],
];

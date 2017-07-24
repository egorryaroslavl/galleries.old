<?php

	return [
		'paginate'             => 30,
		'icons_dir'            => 'upload/icons/galleries/',
		'gallery_dir'          => 'upload/galleries/',
		'image_width'          => 1024,
		'image_height'         => 1024,
		'image_preview_width'  => 220, // ширина превью изображения
		'image_preview_height' => 220, // высота превью изображения
		'icon_max_width'  => 500, //максимально дозволенный размер ширины изображения иконки
		'icon_max_height' => 300, //максимально дозволенный размер высоты изображения иконки
		'icon_width'      => 220, // ширина превью иконки
		'icon_height'     => 220, // высота превью иконки
		'menu'            => [
			'url'   => '/admin/galleries',
			'title' => 'Галереи',
			'icon'  => 'fa fa-camera',
			'pages' => []
		],
	];

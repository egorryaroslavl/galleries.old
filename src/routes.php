<?php


	/*=============  GALLERIES  ==============*/

	Route::group( [ 'middleware' => 'web' ], function (){

		Route::get( '/admin/galleries', 'egorryaroslavl\galleries\GalleriesController@index' );
		Route::get( '/admin/galleries/create', 'egorryaroslavl\galleries\GalleriesController@create' );



		Route::get( '/admin/galleries/{id}/edit', 'egorryaroslavl\galleries\GalleriesController@edit' );
		Route::get( '/admin/galleries/{id}/delete', 'egorryaroslavl\galleries\GalleriesController@destroy' );



		Route::post( '/admin/galleries/store', 'egorryaroslavl\galleries\GalleriesController@store' )->name( 'galleries-store' );
		Route::post( '/admin/galleries/update', 'egorryaroslavl\galleries\GalleriesController@update' )->name( 'galleries-update' );



		Route::post( '/translite', 'egorryaroslavl\Admin\AdminController@translite' );
		Route::post( '/changestatus', 'egorryaroslavl\galleries\GalleriesController@changestatus' )->name( 'changestatus' );
		Route::post( '/reorder', 'egorryaroslavl\galleries\GalleriesController@reorder' )->name( 'reorder' );


	} );

	Route::post( '/imgsave', 'egorryaroslavl\galleries\GalleriesController@imgsave' );
	Route::post( '/galleryget', 'egorryaroslavl\galleries\GalleriesController@galleryGetById' );
	Route::post( '/imgremove', 'egorryaroslavl\galleries\GalleriesController@imgremove' );

	/*=============  /GALLERIES  ==============*/


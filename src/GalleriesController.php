<?php

	namespace Egorryaroslavl\Galleries;

	use App\Http\Controllers\Controller;
	use Egorryaroslavl\Galleries\Models\GalleryModel;
	use Illuminate\Http\Request;
	use Illuminate\Validation\Rule;


	//use Intervention\Image\Facades\Image;


	class GalleriesController extends Controller
	{


		function messages()
		{
			$strLimit = config( 'admin.settings.text_limit.text_short_description.', 300 );
			return [
				'name.required'         => 'Поле "Имя" обязятельно для заполнения!',
				'alias.required'        => 'Поле "Алиас" обязятельно для заполнения!',
				'name.unique'           => 'Значение поля "Имя" не является уникальным!',
				'alias.unique'          => 'Значение поля "Алиас" не является уникальным!',
				'description.required'  => 'Поле "Текст" обязятельно для заполнения!',
				'short_description.max' => 'Поле "Краткий текст" не должно быть более ' . $strLimit . ' символов!',

			];

		}

		public function index()
		{

			$data        = GalleryModel::orderBy( 'pos', 'ASC' )
				->paginate( config( 'admin.galleries.paginate' ) );
			$data->table = 'galleries';
			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Галереи</h2><ol class="breadcrumb"><li><a href="/admin">Главная</a></li><li class="active"><a href="/admin/galleries">Галереи</a></li></ol></div></div>';


			return view( 'galleries::index',
				[
					'data'        => $data,
					'breadcrumbs' => $breadcrumbs
				] );


		}


		public function create()
		{
			$data        = new GalleryModel();
			$data->act   = 'galleries-store';
			$data->table = 'galleries';

			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Галереи</h2><ol class="breadcrumb"><li><a href="/admin">Главная</a></li><li class="active"><a href="/admin/galleries">Галереи</a></li><li><strong>Создание новой категории</strong></li></ol></div></div>';

			return view( 'galleries::form', [ 'data' => $data, 'breadcrumbs' => $breadcrumbs ] );


		}


		public function edit( $id )
		{

			$galleryModel = GalleryModel::class;
			$data         = $galleryModel::where( 'id', $id )->first();

			if( is_null( $data ) ){
				return redirect( '/admin/galleries' );
			};

			$data->table = 'galleries';
			$data->act   = 'galleries-update';


			if( !file_exists( public_path( $data->icon ) )
				|| empty( $data->icon )
				|| !isset( $data->icon )
			){
				$data->icon = null;
			}

			$galleryData = $data->gallery;

			$gallery = view( 'galleries::gallery_list', [ 'data' => $galleryData ] );


			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Галереи</h2><ol
class="breadcrumb"><li><a href="/admin">Главная</a></li><li
class="active"><a href="/admin/galleries">Галереи</a></li><li>Редактирование <strong>[
 <a href="/galleries/' . $data->alias . '" style="color:blue" title="Смотреть на пользовательской части">' . $data->name . ' <img src="/_admin/img/extlink.png" alt=""
 style="margin:0"></a> ]</strong></li></ol></div></div>';

			return view( 'galleries::form', [
				'data'        => $data,
				'breadcrumbs' => $breadcrumbs,
				'gallery'     => $gallery
			] );
		}


		public function store( Request $request )
		{

			//dd( $request->all() );

			$v = \Validator::make( $request->all(), [
				'name' => 'required|unique:galleries|max:255',
			], $this->messages() );


			if( $v->fails() ){
				return redirect( 'admin/galleries/create' )
					->withErrors( $v )
					->withInput();
			}


			$input        = $request->all();
			$input        = array_except( $input, '_token' );
			$galleryModel = GalleryModel::create( $input );
			$id           = $galleryModel->id;


			/* Если есть галерея, обрабатываем её  */
			$galleryNewArr = $this->image_processing( $request );
			//dd($galleryNewArr);
			/* Теперь обновим поле icon именем с id */
			$gallery          = GalleryModel::find( $id );
			$gallery->gallery = $galleryNewArr;
			$gallery->save();


			\Session::flash( 'message', 'Запись добавлена!' );

			if( isset( $request->submit_button_stay ) ){
				return redirect()->back();
			}
			return redirect( '/admin/galleries' );


		}


		public function update( Request $request )
		{
			/* Определяем куда редиректить после выполнения */
			$direct = isset( $request->submit_button_stay ) ? 'stay' : 'back';
			/* если в имени файла нет 'upload' значит от ещё в /tmp */

			/* Если есть галерея, обрабатываем её  */
			$galleryNewArr = $this->image_processing( $request );

			//dd($galleryNewArr);
			/*  */
			$v = \Validator::make( $request->all(), [
				'name' => [
					'required',
					Rule::unique( 'galleries' )->ignore( $request->id ),
					'max:255'
				],

				'alias'       => [
					'required',
					Rule::unique( 'galleries' )->ignore( $request->id ),
					'max:255'
				],
				'description' => 'required',


			], $this->messages() );


			/* если есть ошибки - сообщаем об этом */
			if( $v->fails() ){
				return redirect( 'admin/galleries/' . $request->id . '/edit' )
					->withErrors( $v )
					->withInput();
			}

			$service              = GalleryModel::find( $request->id );
			$service->name        = $request->name;
			$service->alias       = $request->alias;
			$service->description = $request->description;
			$service->public      = isset( $request->public ) ? $request->public : 0;
			$service->anons       = isset( $request->anons ) ? $request->anons : 0;
			$service->hit         = isset( $request->hit ) ? $request->hit : 0;


			$storedGallery    = empty( $service->gallery ) ? [] : $service->gallery;
			$service->gallery = array_merge( $storedGallery, $galleryNewArr );

			$service->h1                  = $request->h1;
			$service->metatag_title       = $request->metatag_title;
			$service->metatag_description = $request->metatag_description;
			$service->metatag_keywords    = $request->metatag_keywords;
			$service->save();

			\Session::flash( 'message', 'Запись обновлена!' );


			if( $direct == 'back' ){
				return redirect( url( '/admin/galleries' ) );
			}

			if( $direct == 'stay' ){
				return redirect()->back();
			}


		}

		public function destroy( $id )
		{

			$gallery       = GalleryModel::find( $id );
			$fileSmallPath = public_path() . $gallery->icon;
			$filePath      = str_replace( '_small', '', $fileSmallPath );
			if( file_exists( $fileSmallPath ) ){
				unlink( $fileSmallPath );
			}
			if( file_exists( $filePath ) ){
				unlink( $filePath );
			}
			$gallery->delete();
			return redirect()->back();

		}

		/* Работа с изображениями */

		public function imgsave( Request $request )
		{


			if( $request->hasFile( 'photo' ) ){

				$width       = config( 'admin.galleries.image_width' );
				$height      = config( 'admin.galleries.image_height' );
				$imgid       = $this->imgId();
				$uploads_dir = sys_get_temp_dir(); // системный /tmp
				$file        = $request->file( 'photo' );

				$name = $this->newName( $request );

				/* Помещаем файл во временную директорию.
				При сохранении остальных данных заберём его оттуда */
				$img = \Image::make( $file )
					->widen( $width, function ( $constraint ){
						$constraint->upsize();
					} )
					->heighten( $height, function ( $constraint ){
						$constraint->upsize();
					} )->save( $uploads_dir . '/' . $name );


				if( $img ){

					//	$data = (string)$img->encode( 'data-url' );
					return [ 'success'  => true,
					         'error'    => 'ok',
					         'imgid'    => $imgid,
					         'tmp_path' => $uploads_dir . '/' . $name ];
				}
			}
		}

		public function imgremove( Request $request )
		{
			/*  "gallery_id" => "1"
  "img" => "/upload/galleries/galleries_1_pszs-qcy1-ndct-ed8w.jpeg"*/

			//$baseName = basename( $request->img );
			$newArray = [];
			$imgid    = $this->parseName( basename( $request->img ) )[ 'img_id' ];
			//dd( $this->imagesById($request->gallery_id) );

			$a_galleryImgArray = $this->imagesById( $request->gallery_id );

			if( count( $a_galleryImgArray ) > 0 ){

				foreach( $a_galleryImgArray as $galleryImg ){

					if( $galleryImg[ 'imgid' ] != $imgid ){

						$newArray[] = $galleryImg;

					} else{

						if( file_exists( public_path( $galleryImg[ 'name' ] ) ) ){

							unlink( public_path( $galleryImg[ 'name' ] ) );

						}

						if( file_exists( public_path( $galleryImg[ 'name_small' ] ) ) ){

							unlink( public_path( $galleryImg[ 'name_small' ] ) );

						}

					}

				}

			}
			$gallery          = GalleryModel::find( $request->gallery_id );
			$gallery->gallery = $newArray;
			$gallery->save();

			return json_encode( [ 'success' => true, 'imgid' => $imgid ] );


		}

		public function imagesById( $id )
		{

			$gallery = GalleryModel::find( $id );
			return $gallery->gallery;

		}


		public function image_processing( $request )
		{


			if( isset( $request[ 'new' ] ) // если есть свойство gallery
				&& !empty( $request[ 'new' ] ) // если оно не пусто
				&& is_array( $request[ 'new' ] ) // если это массив
				&& $count = count( $request[ 'new' ] ) > 0 // если массив не пуст
			){

				//	dd( $request->gallery );
				$galleyArray = $request[ 'new' ];

				/* обходим массив в поисках вновь добавленых файлов */
				foreach( $galleyArray as $file ){

					if(
						isset( $file ) // если есть индекс name
						&& !empty( $file ) // и он не пуст
						&& preg_match( '%tmp/%', $file ) // и в пути есть tmp/%
					){
						/* отправляем на обработку */
						$filesNewArr[] = $this->imgGalleryMaking( $request, $file );

					}

				}

				return $filesNewArr;


			}

			return [];

		}

		/**
		 * Получает массив с путём к новому файлу
		 *
		 * @param $file
		 *
		 * @return array
		 */
		public function imgGalleryMaking( $request, $file )
		{

			//dd([$request, $file ]);

			$baseName = basename( $file );
			/*
			 * Временный файл имеет в имени:
			 * 1 имя раздела: galleries
			 * 2 ID изображения
			 * 3 Токен Laravel: $request->_token
			 * 4 Постфикс "_small"
			  * */
			/* разбираем имя */

			$fileNameParts = $this->parseName( $baseName );
			//dd($fileNameParts);
			$imgid = $fileNameParts[ 'img_id' ];


			/* в /tmp файл имеет в имени _token Меняем его не id категории и прибавляем к нему путь для сохранения иконок */
			$fileName = $this->fileName( $baseName, $request );
			/* также делаем для превью */
			$fileNameSmall = $this->fileSmallName( $fileName );
			//dd( [ $fileName, $fileNameSmall ] );
			/* абсоютный путь */
			$filePath = public_path( $fileName );
			/* абсоютный путь для превью */
			$filePathSmall = public_path( $fileNameSmall );

			\Image::make( $file )
				->save( $filePath )
				->widen( config( 'admin.galleries.image_preview_width' ), function ( $constraint ){
					$constraint->upsize();
				} )
				->heighten( config( 'admin.galleries.image_preview_height' ), function ( $constraint ){
					$constraint->upsize();
				} )->save( $filePathSmall );

			return [
				'name'       => $fileName,
				'name_small' => $fileNameSmall,
				'comment'    => '',
				'imgid'      => $imgid
			];


		}

		public static function changestatus( Request $request )
		{
			$sql = "
			UPDATE `" . $request->table . "`
			SET `" . $request->field . "` = NOT `" . $request->field . "` WHERE id =" . $request->id;

			$res = \DB::update( $sql );

			if( $res > 0 ){
				$current = $request->value > 0 ? '0' : '1';
				echo json_encode( [ 'error' => 'ok', 'message' => $current ] );
			} else{
				echo json_encode( [ 'error' => 'error', 'message' => '' ] );
			}

		}

		public function reorder( Request $request )
		{


			if( isset( $request->sort_data ) ){

				$id        = array();
				$table     = $request->table;
				$sort_data = $request->sort_data;

				parse_str( $sort_data );

				$count = count( $id );
				for( $i = 0; $i < $count; $i++ ){
					\DB::update( 'UPDATE `' . $table . '` SET `pos`=' . $i . ' WHERE `id`=? ', [ $id[ $i ] ] );

				}


			}
		}

		public function galleryGetById( Request $request )
		{
			$filesList = [];

			if( $request->id != 0 ){

				$gallery = GalleryModel::find( $request->id );
				//	dd($gallery );
				if( count( $gallery->gallery ) > 0 ){

					foreach( $gallery->gallery as $item ){

						if( isset( $item[ 'name' ] )
							&& file_exists( public_path( $item[ 'name' ] ) )
							&& file_exists( public_path( $item[ 'name_small' ] ) )

						){

							$filesList[] = [
								'name'       => '/' . $item[ 'name' ],
								'name_small' => '/' . $item[ 'name_small' ],
								'imgid'      => $this->parseName( $item[ 'name' ] )[ 'img_id' ],
								'size'       => filesize( public_path( $item[ 'name' ] ) )

							];


						}


					}
				}


			}

			return $filesList;
		}

		public static function galleryget( Request $request )
		{


			if( $request->id != 0 ){

				$gallery = \DB::table( $request->table )
					->select( [ 'gallery' ] )
					->where( 'id', $request->id )->get();

				$resArr = [

				];


				$galleryArr = json_decode( $gallery[ 0 ]->gallery );

				if( is_array( $galleryArr ) && count( $galleryArr ) > 0 ){

					foreach( $galleryArr as $gallery ){
						$resArr[] = $gallery;
						/*				$resArr[][ 'success' ]      = true;
										$resArr[][ 'name' ]         = $gallery;
										$resArr[][ 'thumbnailUrl' ] = $gallery;
										$resArr[][ 'uuid' ]         = $request->_token;*/
					}

					return $resArr;
				};
			}

		}

		public function imgId()
		{

			return strtolower(
				str_random( 4 )
				. '-' . str_random( 4 )
				. '-' . str_random( 4 )
				. '-' . str_random( 4 ) );

		}

		public function newName( $request )
		{
			//$file        = $request->file( 'photo' );
			$ext = $request->file( 'photo' )->clientExtension();
			return 'galleries_' . $request->_token . '_' . $this->imgId() . '.' . $ext; // новое имя файла

		}

		public function parseName( $baseName )
		{
			$ext           = substr( $baseName, strrpos( $baseName, '.', -1 ), strlen( $baseName ) );
			$baseName      = str_replace( $ext, '', $baseName );
			$fileNameParts = explode( '_', $baseName );
			/*			$tableName     = $fileNameParts[ 0 ];
						$token     = $fileNameParts[ 1 ];
						$imgId     = $fileNameParts[ 2 ];*/
			return [
				'table_name' => $fileNameParts[ 0 ],
				'token'      => $fileNameParts[ 1 ],
				'img_id'     => $fileNameParts[ 2 ]
			];
		}

		public function fileName( $baseName, $request )
		{
			return config( 'admin.galleries.gallery_dir' ) . str_replace( $request->_token, $request->id, $baseName );
		}

		public function fileSmallName( $bigFileName )
		{
			$ext = substr( $bigFileName, strrpos( $bigFileName, '.', -1 ), strlen( $bigFileName ) );
			return str_replace( $ext, '_small' . $ext, $bigFileName );

		}
	}

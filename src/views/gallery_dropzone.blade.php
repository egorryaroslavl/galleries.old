<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.1/min/dropzone.min.js"></script>
<div class="row table table-striped files" id="previews">
	<div id="template" class="file-row">
		
		<div class="col-md-3">
			<div class="ibox-content text-center">
				
				<div class="m-b-sm preview" style="position: relative;height:120px">
					<button
						class="btn btn-danger btn-circle btn-sm img-remove"
						style="position: absolute; top:0; right:10px"
						data-gallery_id="{{$data->id,"0"}}"
						type="button" data-dz-remove-><i class="fa fa-trash"></i></button>
					<img alt="" style="height:120px" data-dz-thumbnail class="img-thumbnail">
				
				</div>
			
			</div>
		</div>
	</div>
</div>
<style>
	.image-comment textarea {
		height:      4.0rem;
		text-align:  center;
		padding:     2px;
		margin:      0;
		font-size:   10px;
		line-height: 1.0rem
		}
</style>
<script>

	var previewNode = document.querySelector( "#template" );
	previewNode.id = "";
	var previewTemplate = previewNode.parentNode.innerHTML;
	previewNode.parentNode.removeChild( previewNode );

	var imgDropzone = new Dropzone( '#previews', {
		url              : "/imgsave",
		paramName        : "photo",
		parallelUploads  : 20,
		/*		thumbnailWidth   : 120,
				thumbnailHeight  : 120,*/
		/*		resizeWidth      : 120,
				resizeHeight     : 120,*/
		params           : {
			table  : "{{$data->table}}",
			id     : "{{$data->id,"0"}}",
			gallery: true,
			_token : "{{csrf_token()}}"
		},
		previewTemplate  : previewTemplate,
		autoQueue        : true,
		autoProcessQueu  : true,
		previewsContainer: "#previews",
		clickable        : ".fileinput-button",
		init             : function(){

			var imgDropzone = this;

			$.ajax( {
				type   : "POST",
				url    : "/galleryget",
				data   : {
					id:{{$data->id}}
				},
				success: function( fileList ){
					$.each( fileList, function( i, val ){
						var mockFile = val;
						imgDropzone.options.addedfile.call( imgDropzone, mockFile );
						imgDropzone.options.thumbnail.call( imgDropzone, mockFile, val.name_small );
						$( '#previews' ).append( '<input type="hidden" name="gallery[\'' + val.imgid + '\']" value="' + val.name + '" />' );
					} );

				}
			} );
		},
		 	success          : function( file, msg ){
				$( '#previews' ).append( '<input type="hidden" name="new[]" value="' + msg.tmp_path + '" />' );
	
			}
	} );


 

	$( "body" ).on( "click", ".img-remove", function(){

		var _this = $( this );
		var gallery_id = _this.data( 'gallery_id' );
		var img = _this.next().attr( 'alt' );
		$.ajax( {
			type   : "POST",
			url    : "/imgremove",
			data   : {
				gallery_id: gallery_id,
				img       : img
			},
			success: function( msg ){
				var res = jQuery.parseJSON( msg );
				if( res.success ){

					_this.parent().parent().parent().remove();

				}


			}
		} );

	} );
</script>

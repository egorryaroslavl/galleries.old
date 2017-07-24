@isset($data)
	@if(count($data)>0)
		@foreach($data as $item)
			<div class="col-md-3">
				<div class="ibox-content text-center">
					<div class="m-b-sm preview" style="position:relative;height: 220px!important;">
						<button class="btn btn-danger btn-circle btn-sm" style="position: absolute; top:0; right:0" type="button" data-dz-remove><i class="fa fa-trash"></i></button>
						<div><img alt="" src="/" class="img-thumbnail img-responsive" style="height:150px;width:150px"></div>
						<input type="hidden" name="gallery[]" value="{{$item['name']}}"/>
					</div>
					<p class="size" data-dz-size></p>
					<div class="text-center">
						<!--	<a class="btn btn-xs btn-white"><i class="fa fa-thumbs-up"></i> Like </a>-->
					</div>
				</div>
			</div>
		@endforeach
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
	@endif
@endisset



@if ($categories->count() > 0 )
  <div class="container">
            <div class="categories-title m-0">
                <span class="font-semibold">{{ translate('categories')}}</span>
            </div>
    <div class="rowmasonery">
 	   @foreach($categories as $key => $category)
				@if ($key<10)
				 <div class="column masonary-grid">
						<a href="{{route('products',['id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
							<div class="__img">
								<img alt="{{ $category->name }}"
									 src="{{ getStorageImages(path:$category->icon_full_url, type: 'category') }}">
							</div>
							<p class="categoryname-text text-center fs-13 font-semibold mt-2">{{Str::limit($category->name, 12)}}</p>
						</a>
					</div>
				@endif
	   @endforeach
    </div>
  </div>     
@endif

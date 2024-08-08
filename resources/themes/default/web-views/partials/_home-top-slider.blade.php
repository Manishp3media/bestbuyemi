@if(count($main_banner) > 0)
<section class="bg-transparent py-3">
    <div class="container position-relative">
        <div class="row-outer">
            
            <div class="d-flex slider-area">
                <div class="sliderimages">
                    <div class="owl-theme owl-carousel hero-slider">
                        @foreach($main_banner as $key=>$banner)
                            <a href="{{$banner['url']}}" class="d-block" target="_blank">
                                <img class="w-100 __slide-img" alt=""
                                    src="{{ getStorageImages(path: $banner->photo_full_url, type: 'banner') }}">
                            </a>
                        @endforeach
                    </div>
                </div> 
				
				
				<!--@if ($categories->count() > 0 )-->
                <div class="categoryblock">
					<div class="d-flex sliderstaticimages">
						<div class="upperimage">
							<img src="{{asset('myfigma/static_image_upper.png')}}" alt="product image 01"/>
						</div>
						<div class="upperimage">
							<img src="{{asset('/myfigma/static _image_below.png')}}" alt="product image 02"/>
						</div>
                    </div>
					
					
                    <!--<div class="category-menu-wrap position-static ttt">
                        <ul class="category-menu mt-0">
                            @foreach ($categories as $key=>$category)
                                <li>
                                    <a href="{{route('products',['id'=> $category['id'],'data_from'=>'category','page'=>1])}}">{{$category->name}}</a>
                                    @if ($category->childes->count() > 0)
                                        <div class="mega_menu z-2">
                                            @foreach ($category->childes as $sub_category)
                                                <div class="mega_menu_inner">
                                                    <h6><a href="{{route('products',['id'=> $sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_category->name}}</a></h6>
                                                    @if ($sub_category->childes->count() >0)
                                                        @foreach ($sub_category->childes as $sub_sub_category)
                                                            <div><a href="{{route('products',['id'=> $sub_sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_sub_category->name}}</a></div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                            <li class="text-center">
                                <a href="{{route('categories')}}" class="text-primary font-weight-bold justify-content-center text-capitalize">
                                    {{translate('view_all')}}
                                </a>
                            </li>
                        </ul>
                    </div>-->
                </div>
            <!--@endif-->
            </div>
						<div class="textonslider">
				<div class="smartchoice-headingtext">
					<h2 class="headingtextsmart">Smart Choices, Bigger Savings</h2>
				</div>
				<div class="gettextpara">
					<p class="getmoretext">Get more for your money with every purchase!</p>
				</div>
				<div class="shopnowbtn">
					<a href="#" class="btnshop">Shop Now</a>
				</div>
</div>
        </div>
    </div>
</section>
@endif
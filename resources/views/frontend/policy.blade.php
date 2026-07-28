<!DOCTYPE html>
<html lang="en" dir="{!! session()->has('current_lang') && session('current_lang') == 'ar' ? 'rtl' : 'ltr' !!}">

<head>
    @include('frontend.includes.head')
</head>

<body>
    <section id="features" class="ud-features">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title-">
                        <span>{{__('frontend.policies')}}</span>
                        <h2>{{$title}}</h2>
                        <p class="py-5">
                            {!! html_entity_decode($content) !!}
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-sm-12">
                    <a href="{{route('index')}}" class="ud-feature-link">
                        <div class="ud-single-feature wow fadeInUp" data-wow-delay=".1s">
                            <div class="ud-feature-icon">
                                <i class="lni lni-home"></i>
                            </div>
                            <div class="ud-feature-content">
                                <h3 class="ud-feature-title">{{__('frontend.back_to_homepage')}}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== Back To Top Start ====== -->
    <a href="javascript:void(0)" class="back-to-top">
        <i class="lni lni-chevron-up"> </i>
    </a>
    <!-- ====== Back To Top End ====== -->

    <!-- ====== All foot files ====== -->
    @include('frontend.includes.foot')
</body>

</html>

<!DOCTYPE html>
<html lang="en" dir="{!! session()->has('current_lang') && session('current_lang') == 'ar' ? 'rtl' : 'ltr' !!}">

<head>
    @include('frontend.includes.head')
</head>

<body>
    

    @yield('content')

    <!-- ====== Footer Start ====== -->
    @include('frontend.includes.footer')
    <!-- ====== Footer End ====== -->

    <!-- ====== Back To Top Start ====== -->
    <a href="javascript:void(0)" class="back-to-top">
        <i class="lni lni-chevron-up"> </i>
    </a>
    <!-- ====== Back To Top End ====== -->

    <!-- ====== All foot files ====== -->
    @include('frontend.includes.foot')
</body>

</html>

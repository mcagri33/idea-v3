<!doctype html>
<html class="no-js" lang="zxx">

<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>@yield('title')</title>
  <meta name="description" content="WETA - SaaS Landing HTML5 Template">
  <meta name="author" content="ahmmedsabbirbd">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Place favicon.ico in the root directory -->
  <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/imgs/favicon.ico')}}">
  <!-- CSS here -->
  <link rel="stylesheet" href="{{asset('assets/css/vendor/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/vendor/animate.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/plugins/swiper.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/vendor/magnific-popup.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/vendor/fontawesome-pro.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/vendor/spacing.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/plugins/slick.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/plugins/odometer-theme-default.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/main.css')}}">
</head>

<body>

<!--[if lte IE 9]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<!-- preloader start -->
<div id="preloader">
  <div class="preloader-close">x</div>
  <div class="sk-three-bounce">
    <div class="sk-child sk-bounce1"></div>
    <div class="sk-child sk-bounce2"></div>
    <div class="sk-child sk-bounce3"></div>
  </div>
</div>
<!-- preloader start -->

<!-- Backtotop start -->
<div class="backtotop-wrap cursor-pointer">
  <svg class="backtotop-wrap d-none">
    <path class="btn-wrap" d="M0 0 L10 10" />
  </svg>
  <span class="btn-wrap">
        <span class="text-one"><i class="fa-solid fa-angle-up"></i></span>
        <span class="text-two"><i class="fa-solid fa-angle-up"></i></span>
    </span>
</div>
<!-- Backtotop end -->

<!-- Offcanvas area start -->
<div class="fix">
  <div class="offcanvas__area">
    <div class="offcanvas__wrapper">
      <div class="offcanvas__content">
        <div class="offcanvas__top d-flex justify-content-between align-items-center">
          <div class="offcanvas__logo">
            <a href="index.html">
              <img src="{{asset('assets/imgs/logo/logo-white.svg')}}" alt="logo not found">
            </a>
          </div>
          <div class="offcanvas__close">
            <button class="offcanvas-close-icon animation--flip">
                                <span class="offcanvas-m-lines">
                              <span class="offcanvas-m-line line--1"></span><span class="offcanvas-m-line line--2"></span><span class="offcanvas-m-line line--3"></span>
                                </span>
            </button>
          </div>
        </div>
        <div class="mobile-menu fix"></div>
        <div class="offcanvas__social">
          <h4 class="offcanvas__title mb-20">Subscribe & Follow</h4>
          <ul class="header-top-socail-menu d-flex">
            <li><a href="https://www.facebook.com/"><i class="fab fa-facebook-f"></i></a></li>
            <li><a href="https://twitter.com/"><i class="fab fa-twitter"></i></a></li>
            <li><a href="https://www.pinterest.com/"><i class="fa-brands fa-pinterest-p"></i></a></li>
            <li><a href="https://vimeo.com/"><i class="fa-brands fa-vimeo-v"></i></a></li>
          </ul>
        </div>
        <div class="offcanvas__btn d-sm-none">
          <div class="header__btn-wrap">
            <a href="https://themeforest.net/user/rrdevs/portfolio" class="rr-btn rr-btn__theme rr-btn__theme-white mt-40 mt-sm-35 mt-xs-30">
                            <span class="btn-wrap">
                                <span class="text-one">Purchase Now</span>
                                <span class="text-two">Purchase Now</span>
                            </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="offcanvas__overlay"></div>
<div class="offcanvas__overlay-white"></div>
<!-- Offcanvas area start -->

<!-- Header area start -->
@include('site.layouts.header')
<!-- Header area end -->

<!-- Body main wrapper start -->
<main class="position-relative z-1 white-bg">

  @yield('content')

</main>
<!-- Body main wrapper end -->

<!-- Footer area start -->
@include('site.layouts.footer')
<!-- Footer area end -->

<!-- JS here -->
<script src="{{asset('assets/js/vendor/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/waypoints.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/meanmenu.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/odometer.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/swiper.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/wow.js')}}"></script>
<script src="{{asset('assets/js/vendor/magnific-popup.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/type.js')}}"></script>
<script src="{{asset('assets/js/plugins/imagesloaded-pkgd.js')}}"></script>
<script src="{{asset('assets/js/plugins/isotope-pkgd.js')}}"></script>
<script src="{{asset('assets/js/plugins/counterup.js')}}"></script>
<script src="{{asset('assets/js/plugins/nice-select.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/parallax.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/parallax-scroll.js')}}"></script>
<script src="{{asset('assets/js/plugins/gsap.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/ScrollTrigger.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/SplitText.js')}}"></script>
<script src="{{asset('assets/js/plugins/slick.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/ajax-form.js')}}"></script>
<script src="{{asset('assets/js/main.js')}}"></script>
</body>

</html>

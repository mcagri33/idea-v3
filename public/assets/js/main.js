(function ($) {
    "use strict";
    var windowOn = $(window);


    /*-----------------------------------------------------------------------------------

        Template Name: WETA - SaaS Landing HTML5 Template
        Author: RRDevs
        Support: https://support.rrdevs.net
        Description: WETA - SaaS Landing HTML5 Template.
        Version: 1.0
        Developer: Sabbir Ahmmed (https://github.com/ahmmedsabbirbd)

    -----------------------------------------------------------------------------------
 /*======================================
   Data Css js
   ========================================*/
    $("[data-background]").each(function() {
        $(this).css(
            "background-image",
            "url( " + $(this).attr("data-background") + "  )"
        );
    });

    $("[data-width]").each(function() {
        $(this).css("width", $(this).attr("data-width"));
    });

    class GSAPAnimation {
        static Init() {
            this.banner2Animation()
            /*play-video-animation*/
            $('.play-video-animation').length && this.imageAnimation('.play-video-animation')
            /*play-video-animation*/
            $('.brand-image-animation').length && this.imageAnimation('.brand-image-animation')
            /*case-studies-image-animation*/
            $('.case-studies-image-animation').length && this.imageAnimation('.case-studies-image-animation')
            /*what-we-do-image-animation*/
            $('.what-we-do-image-animation').length && this.imageAnimation('.what-we-do-image-animation')
            /*feedback__item-customer-animation*/
            $('.contact-us__media-animation').length && this.imageAnimation('.contact-us__media-animation')
            /*feedback__item-customer-animation*/
            $('.cta__media-animation').length && this.imageAnimation('.cta__media-animation')
            /*specialists-image-animation*/
            $('.specialists-image-animation').length && this.imageAnimation('.specialists-image-animation')
            /*popup-video-button_animation*/
            $('.popup-video-button_animation').length && this.buttonRotateAnimation('.popup-video-button_animation')
            /*section-title-2-animation*/
            $('.section-title-2-animation').length && this.sectionTitleAnimation('.section-title-2-animation');
            /*section-title-2-animation*/
            $('.section-subTile-2-animation').length && this.sectionSubTitleAnimation('.section-subTile-2-animation');
            /*section-title-2-animation*/
            $('.section-button-animation').length && this.sectionbuttonAnimation('.section-button-animation');
            /*section-title-2-animation*/
            $('.section-input-animation').length && this.sectionInputAnimation('.section-input-animation');
        }

        static banner2Animation() {
            const banner2 = gsap.timeline();

            const banner2Title = document.querySelector(".banner-2__title");
            const banner2Title2 = document.querySelector(".banner-2__title-2");
            const banner2Title3 = document.querySelector(".banner-2__title-3");
            const banner2Title4 = document.querySelector(".banner-2__title-4");
            const banner2Image = document.querySelector(".banner-2__image img");
            const banner2Image1 = document.querySelector(".banner-2__image-1");
            const banner2Image2 = document.querySelector(".banner-2__image-2");
            const banner2Image3 = document.querySelector(".banner-2__image-3");
            const banner2Image4 = document.querySelector(".banner-2__image-4");

            if(banner2Title) {
                gsap.set(banner2Image, { opacity: 0, scale: 0, y: 1 });
                gsap.set(banner2Image1, { opacity: 0, scale: 0, y: 1 });
                gsap.set(banner2Image2, { opacity: 0, scale: 0, y: 1 });
                gsap.set(banner2Image3, { opacity: 0, scale: 0, y: 1 });
                gsap.set(banner2Image4, { opacity: 0, scale: 0, y: 1 });

                const split_banner2Title = new SplitText(banner2Title, { type: "chars" });
                const split_banner2Title2 = new SplitText(banner2Title2, { type: "chars" });
                const split_banner2Title3 = new SplitText(banner2Title3, { type: "chars" });
                const split_banner2Title4 = new SplitText(banner2Title4, { type: "chars" });

                banner2.from(split_banner2Title.chars, { duration: 1, x: 70, autoAlpha: 0, stagger: 0.1 });
                banner2.to(banner2Image, { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }, "-=1.8");
                banner2.from(split_banner2Title2.chars, { duration: 1, x: 70, autoAlpha: 0, stagger: 0.1 }, "-=1.5");
                banner2.to(banner2Image1, { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }, "-=1.8");
                banner2.to(banner2Image2, { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }, "-=1.8");
                banner2.to(banner2Image3, { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }, "-=1.8");
                banner2.to(banner2Image4, { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }, "-=1.8");
                banner2.from(split_banner2Title4.chars, { duration: 0.7, x: 70, autoAlpha: 0, stagger: 0.1 }, "-=1.8");
                banner2.from(split_banner2Title3.chars, { duration: 1, x: 70, autoAlpha: 0, stagger: 0.1 }, "-=1");
                return banner2;
            }
        }
        static imageAnimation(activeClass) {
            const imageGsap = gsap.timeline({
                scrollTrigger: {
                    trigger: activeClass,
                    start: 'top 90%',
                    end: 'bottom 60%',
                    scrub: false,
                    markers: false,
                    toggleActions: 'play none none none'
                }
            });
            imageGsap.from(activeClass, {
                opacity: 0,
                y: 20,
                scale: 0.8,
                duration: 0.8
            });
            return imageGsap;
        }
        static buttonRotateAnimation(activeClass) {
            const buttonGsap = gsap.timeline({
                scrollTrigger: {
                    trigger: activeClass,
                    start: 'top 90%',
                    end: 'bottom 60%',
                    scrub: false,
                    markers: false,
                    toggleActions: 'play none none none'
                }
            });
            buttonGsap.from(activeClass, {
                opacity: 0,
                y: 20,
                scale: 0.8,
                rotate: -360,
                duration: 0.8
            });
            return buttonGsap;
        }
        static sectionTitleAnimation(activeClass) {
            let sectionTitleLines = gsap.utils.toArray(activeClass);

            sectionTitleLines.forEach(sectionTextLine => {
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: sectionTextLine,
                        start: 'top 90%',
                        end: 'bottom 60%',
                        scrub: false,
                        markers: false,
                        toggleActions: 'play none none none'
                    }
                });

                const itemSplitted = new SplitText(sectionTextLine, { type: "words, lines" });
                gsap.set(sectionTextLine, { perspective: 400 });
                itemSplitted.split({ type: "lines" })
                tl.from(itemSplitted.lines, { duration: 1, delay: 0.3, opacity: 0, rotationX: -80, force3D: true, transformOrigin: "top center -50", stagger: 0.1 });
            });
        }
        static sectionSubTitleAnimation(activeClass) {
            let sectionTitleLines = gsap.utils.toArray(activeClass);

            sectionTitleLines.forEach(sectionTextLine => {
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: sectionTextLine,
                        start: 'top 90%',
                        end: 'bottom 60%',
                        scrub: false,
                        markers: false,
                        toggleActions: 'play none none none'
                    }
                });
                tl.set(sectionTextLine, {  opacity: 0, y: 20 });
                tl.to(sectionTextLine, { duration: 1, delay: 0.3, y:0, opacity: 1 });
            });
        }
        static sectionbuttonAnimation(activeClass) {
            let sectionButtonLines = gsap.utils.toArray(activeClass);

            sectionButtonLines.forEach(sectionButtonLine => {
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: sectionButtonLine,
                        start: 'top 90%',
                        end: 'bottom 60%',
                        scrub: false,
                        markers: false,
                        toggleActions: 'play none none none'
                    }
                });
                tl.set(sectionButtonLine, {  opacity: 0, y: 20 });
                tl.to(sectionButtonLine, { duration: 1, delay: 0.3, y:0, opacity: 1 });
            });
        }
        static sectionInputAnimation(activeClass) {
            let sectionInputLines = gsap.utils.toArray(activeClass);

            sectionInputLines.forEach(sectionInputLine => {
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: sectionInputLine,
                        start: 'top 90%',
                        end: 'bottom 60%',
                        scrub: false,
                        markers: false,
                        toggleActions: 'play none none none'
                    }
                });
                tl.set(sectionInputLine, {  opacity: 0, y: 20 });
                tl.to(sectionInputLine, { duration: 1, delay: 0.3, y:0, opacity: 1 });
            });
        }

        static Init() {
            /*title-animation*/
            $('.title-animation').length && this.sectionTitleAnimation('.title-animation');
        }
        
        static sectionTitleAnimation(activeClass) {
            let sectionTitleLines = gsap.utils.toArray(activeClass);

            sectionTitleLines.forEach(sectionTextLine => {
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: sectionTextLine,
                        start: 'top 90%',
                        end: 'bottom 60%',
                        scrub: false,
                        markers: false,
                        toggleActions: 'play none none none'
                    }
                });

                const itemSplitted = new SplitText(sectionTextLine, { type: "chars, words" });
                gsap.set(sectionTextLine, { perspective: 100 });
                itemSplitted.split({ type: "words" })
                tl.from(itemSplitted.words, {
                    opacity: 0, 
                    autoAlpha: 0, 
                    transformOrigin: "top center -50",
                    y: "10px",
                    duration: 0.9,
                    stagger: 0.2,
                    ease: "power2.out",
                });
            });
        }
    }

    class RRDEVS {
        static LoadedAfter() {
            $('#preloader').delay(1).fadeOut(500);

            /*Counter Js*/
            $(".counter").counterUp({
                delay: 10,
                time: 1000,
            });

            $(".odometer").waypoint(
                function () {
                    var odo = $(".odometer");
                    odo.each(function () {
                        var countNumber = $(this).attr("data-count");
                        var element = $(this);
                        setTimeout(function() {
                            element.html(countNumber);
                        }, 1000); // 1000 milliseconds delay (1 second)
                    });
                },
                {
                    offset: "80%",
                    triggerOnce: true,
                }
            );

            /*Wow Js*/
            if ($('.wow').length) {
                var wow = new WOW({
                    boxClass: 'wow',
                    animateClass: 'animated',
                    offset: 0,
                    mobile: false,
                    live: true
                });
                wow.init();
            }

            /*GSAPAnimation*/
            GSAPAnimation.Init();
        }
    }

    /*======================================
      Preloader activation
      ========================================*/
    $(window).on('load', RRDEVS.LoadedAfter);
    $(".preloader-close").on("click",  RRDEVS.LoadedAfter)

    window.addEventListener('resize', function() {
        gsap.globalTimeline.clear();
    });


    if ($('.rr-portfolio-load-more').length > 0) {
        $('.grid').imagesLoaded(function () {
            // init Isotope
            var $grid = $('.grid').isotope({
                itemSelector: '.grid-item',
                percentPosition: true,
                masonry: {
                    // use outer width of grid-sizer for columnWidth
                    columnWidth: '.grid-item',
                }
            });


            // filter items on button click
            $('.masonary-menu').on('click', 'button', function () {
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
            });

            //for menu active class
            $('.masonary-menu button').on('click', function (event) {
                $(this).siblings('.active').removeClass('active');
                $(this).addClass('active');
                event.preventDefault();
            });

            var show_item = $('.rr-portfolio-load-more').attr('data-show');
            var count_item = show_item;
            var isotop_call = $grid.data('isotope');

            loadMore(show_item);

            function loadMore(showing) {
            }

            $("#rr-load-more").on('click', function() {
                if ($('.masonary-menu').data('clicked')) {

                    count_item = show_item;
                    $('.masonary-menu').data('clicked', false);
                } else {
                    count_item = count_item;
                };

                count_item = count_item + show_item;

                loadMore(count_item);
            });

            $(".masonary-menu").on('click', function() {
                $(this).data('clicked', true);

                loadMore(show_item);
            });

        });
    }else{
        $('.grid').imagesLoaded(function () {
            // init Isotope
            var $grid = $('.grid').isotope({
                itemSelector: '.grid-item',
                percentPosition: true,
                masonry: {
                    // use outer width of grid-sizer for columnWidth
                    columnWidth: '.grid-item',
                }
            });


            // filter items on button click
            $('.masonary-menu').on('click', 'button', function () {
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
            });

            //for menu active class
            $('.masonary-menu button').on('click', function (event) {
                $(this).siblings('.active').removeClass('active');
                $(this).addClass('active');
                event.preventDefault();
            });

        });

        var show_item_2 = $('.rr-load-item-count').attr('data-show');
        $(".rr-load-item").hide();
        $(".rr-load-item").slice(0, show_item_2).show();
        $("body").on('click touchstart', '.load-more', function (e) {
            e.preventDefault();
            $(".rr-load-item:hidden").slice(0, show_item_2).slideDown();
            if ($(".rr-load-item:hidden").length == 0) {
                $(".load-more").css('display', 'none');
            }

        });
    }

    /*======================================
      Mobile Menu Js
      ========================================*/
    $("#mobile-menu").meanmenu({
        meanMenuContainer: ".mobile-menu",
        meanScreenWidth: "991",
        meanExpand: ['<i class="fa-regular fa-angle-right"></i>'],
    });

    /*======================================
      Sidebar Toggle
      ========================================*/
    $(".offcanvas__close,.offcanvas__overlay").on("click", function () {
        $(".offcanvas__area").removeClass("info-open");
        $(".offcanvas__overlay").removeClass("overlay-open");
    });
    // Scroll to bottom then close navbar
    $(window).scroll(function(){
        if($("body").scrollTop() > 0 || $("html").scrollTop() > 0) {
            $(".offcanvas__area").removeClass("info-open");
            $(".offcanvas__overlay").removeClass("overlay-open");
        }
    });
    $(".sidebar__toggle").on("click", function () {
        $(".offcanvas__area").addClass("info-open");
        $(".offcanvas__overlay").addClass("overlay-open");
    });

    /*======================================
      Body overlay Js
      ========================================*/
    $(".body-overlay").on("click", function () {
        $(".offcanvas__area").removeClass("opened");
        $(".body-overlay").removeClass("opened");
    });

    /*======================================
      Sticky Header Js
      ========================================*/
    $(window).scroll(function () {
        if ($(this).scrollTop() > 80) {
            $("#header-sticky").addClass("rr-sticky");
        } else {
            $("#header-sticky").removeClass("rr-sticky");
        }
    });

    /*======================================
      MagnificPopup image view
      ========================================*/
    $(".popup-image").magnificPopup({
        type: "image",
        gallery: {
            enabled: true,
        },
    });

    /*======================================
      MagnificPopup video view
      ========================================*/
    $(".popup-video").magnificPopup({
        type: "iframe",
    });

    /*======================================
      Button scroll up js
      ========================================*/
        var progressPath = document.querySelector(".backtotop-wrap path");
        var pathLength = progressPath.getTotalLength();
        progressPath.style.transition = progressPath.style.WebkitTransition =
            "none";
        progressPath.style.strokeDasharray = pathLength + " " + pathLength;
        progressPath.style.strokeDashoffset = pathLength;
        progressPath.getBoundingClientRect();
        progressPath.style.transition = progressPath.style.WebkitTransition =
            "stroke-dashoffset 10ms linear";
        var updateProgress = function() {
            var scroll = $(window).scrollTop();
            var height = $(document).height() - $(window).height();
            var progress = pathLength - (scroll * pathLength) / height;
            progressPath.style.strokeDashoffset = progress;
        };
        updateProgress();
        $(window).scroll(updateProgress);
        var offset = 150;
        var duration = 550;
        jQuery(window).on("scroll", function() {
            if (jQuery(this).scrollTop() > offset) {
                jQuery(".backtotop-wrap").addClass("active-backtotop");
            } else {
                jQuery(".backtotop-wrap").removeClass("active-backtotop");
            }
        });
        jQuery(".backtotop-wrap").on("click", function(event) {
            event.preventDefault();
            jQuery("html, body").animate({
                scrollTop: 0
            }, duration);
            return false;
        });

    /*======================================
	One Page Scroll Js
	========================================*/
    /*** Scroll Nav */
    var link = $('.onepagenav #mobile-menu ul li a, .onepagenav .mean-nav ul li a');

    link.on('click', function(e) {
        var target = $($(this).attr('href'));
        $('html, body').animate({
            scrollTop: target.offset().top - 76
        }, 600);
        $(this).parent().addClass('active');
        e.preventDefault();
    });

    $(window).on('scroll', function(){
        scrNav();
    });

    function scrNav() {
        var sTop = $(window).scrollTop();
        $('section').each(function() {
            var id = $(this).attr('id'),
                offset = $(this).offset().top-1,
                height = $(this).height();
            if(sTop >= offset && sTop < offset + height) {
                link.parent().removeClass('active');
                $('.main-menu').find('[href="#' + id + '"]').parent().addClass('active');
            }
        });
    }
    scrNav();

    /*======================================
	Smoth animatio Js
	========================================*/
    $(document).on('click', '.smoth-animation', function (event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: $($.attr(this, 'href')).offset().top - 50
        }, 300);
    });

    ////////////////////////////////////////////////////
    // 21. Counter Js
    if(document.querySelector('.knob')) {
        new PureCounter();
    }

    // Jquery Appear raidal
    if (typeof ($.fn.knob) != 'undefined') {
        $('.knob').each(function () {
            var $this = $(this),
                knobVal = $this.attr('data-rel');

            $this.knob({
                'draw': function () {
                    $(this.i).val(this.cv + '%')
                }
            });

            $this.appear(function () {
                $({
                    value: 0
                }).animate({
                    value: knobVal
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function () {
                        $this.val(Math.ceil(this.value)).trigger('change');
                    }
                });
            }, {
                accX: 0,
                accY: -150
            });
        });
    }

    /*brand__active***/
    let brand = new Swiper(".brand__active", {
        slidesPerView: 1,
        spaceBetween: 156,
        loop: true,
        roundLengths: true,
        clickable: true,
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1601: {
                slidesPerView: 6,
            },
            1401: {
                slidesPerView: 5,
            },
            1200: {
                slidesPerView: 4,
            },
            992: {
                slidesPerView: 3,
            },
            576: {
                spaceBetween: 30,
                slidesPerView: 3,
            },
            481: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*header-3_top-slider***/
    let header3TopSlider = new Swiper(".header-3_top-slider", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        navigation: {
            prevEl: ".header-3_top-slider__arrow-prev",
            nextEl: ".header-3_top-slider__arrow-next",
        },
        roundLengths: true,
        clickable: true,
        autoplay: {
            delay: 3000,
        }
    });

    /*case-studies__active***/
    let caseStudies = new Swiper(".case-studies__active", {
        slidesPerView: 1,
        spaceBetween: 40,
        loop: false,
        roundLengths: true,
        clickable: true,
        scrollbar: {
            el: ".case-studies__scrollbar",
            hide: true,
        },
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            501: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*recent-works__active***/
    let recentWorksActive = new Swiper(".recent-works__active", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: false,
        roundLengths: true,
        clickable: true,
        scrollbar: {
            el: ".recent-works__scrollbar",
            hide: true,
        },
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            611: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*specialists__active***/
    let specialistsActive = new Swiper(".specialists__active", {
        slidesPerView: 1,
        spaceBetween: 40,
        loop: false,
        roundLengths: true,
        clickable: true,
        navigation: {
            prevEl: ".specialists__slider-arrow-prev",
            nextEl: ".specialists__slider-arrow-next",
        },
        scrollbar: {
            el: ".specialists__scrollbar",
            hide: true,
        },
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1200: {
                slidesPerView: 4,
            },
            992: {
                slidesPerView: 3,
            },
            481: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*what-we-do__active***/
    let whatWeDoActive = new Swiper(".what-we-do__active", {
        slidesPerView: 1,
        spaceBetween: 40,
        loop: false,
        roundLengths: true,
        clickable: true,
        navigation: {
            prevEl: ".what-we-do__slider-arrow-prev",
            nextEl: ".what-we-do__slider-arrow-next",
        },
        scrollbar: {
            el: ".what-we-do__scrollbar",
            hide: true,
        },
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1400: {
                slidesPerView: 4,
            },
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*cool-amazing__slider***/
    var coolAmazing__slider = new Swiper(".cool-amazing__slider", {
        slidesPerView: 5,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        pagination: {
            el: ".cool-amazing__slider-dot",
            clickable: true,
        },
        breakpoints: {
            1200: {
                slidesPerView: 5,
            },
            768: {
                slidesPerView: 3,
            },
            0: {
                slidesPerView: 2,
            },
        },
    });

    /*client-testimonial__slider-thubnail***/
    $('.client-testimonial__slider-thubnail').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        spaceBetween: 30,
        centerPadding: "0px",
        centerMode:true,
        arrows: false,
        fade: false,
        asNavFor: '.client-testimonial__slider-nav, .client-testimonial__slider-content',
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 1,
                    centerPadding: "200px",
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    centerPadding: "100px",
                }
            },
            {
                breakpoint: 481,
                settings: {
                    slidesToShow: 1,
                    centerPadding: "50px",
                }
            },
            {
                breakpoint: 381,
                settings: {
                    slidesToShow: 1,
                    centerPadding: "0",
                }
            },
            {
                breakpoint: 0,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });

    /*client-testimonial__slider-content***/
    $('.client-testimonial__slider-content').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        spaceBetween: 30,
        arrows: false,
        fade: false,
        dots: false,
        asNavFor: '.client-testimonial__slider-nav, .client-testimonial__slider-thubnail',
    });

    /*client-testimonial__slider-nav***/
    $('.client-testimonial__slider-nav').slick({
        slidesToShow: 3,
        vertical: true,
        slidesToScroll: 1,
        asNavFor: '.client-testimonial__slider-content, .client-testimonial__slider-thubnail',
        focusOnSelect: true,
        verticalSwiping: true,
        centerPadding: "0px",
        centerMode:true,
        arrows: false,
        spaceBetween: 30,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 5,
                    vertical: false,
                    focusOnSelect: false,
                    verticalSwiping: false,
                }
            },
            {
                breakpoint: 381,
                settings: {
                    slidesToShow: 3,
                    vertical: false,
                    focusOnSelect: false,
                    verticalSwiping: false,
                }
            },
            {
                breakpoint: 0,
                settings: {
                    slidesToShow: 1,
                    vertical: false,
                    focusOnSelect: false,
                    verticalSwiping: false,
                }
            }
        ]
    });

    /*feedback__active***/
    let feedback__active = new Swiper(".feedback__active", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        navigation: {
            prevEl: ".feedback__item__button-prev",
            nextEl: ".feedback__item__button-next",
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*decision-making__active***/
    let decisionMakingActive = new Swiper(".decision-making__active", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        navigation: {
            prevEl: ".decision-making__slider-prev",
            nextEl: ".decision-making__slider-next",
        },
    });

    /*customer-feedback__active***/
    let customerFeedback__active = new Swiper(".customer-feedback__active", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        pagination: {
            el: ".customer-feedback__dot",
            clickable: true,
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*blog-2__active***/
    let blog2 = new Swiper(".blog-2__active", {
        slidesPerView: 3,
        spaceBetween: 40,
        loop: true,
        autoplay: {
            delay: 3000,
        },
        pagination: {
            el: ".blog-2__dot",
            clickable: true,
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            992: {
                slidesPerView: 2,
            },
            901: {
                slidesPerView: 3,
            },
            701: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    /*** lastNobullet */
    function lastNobullet() {
        var lastElement = false;
        $(".footer__copyright-menu ul li, .last_item_not_horizental_bar .col-lg-4").each(function() {
            if (lastElement && lastElement.offset().top != $(this).offset().top) {
                $(lastElement).addClass("no_bullet");
            } else {
                $(lastElement).removeClass("no_bullet");
            }
            lastElement = $(this);
        }).last().addClass("no_bullet");
    };
    lastNobullet();

    $(window).resize(function(){
        lastNobullet();
    });
    let $scene = $('.parallax-element').parallax({
        scalarX: 100,
        scalarY: 100,
    });
    let $scene1 = $('.parallax-element-1').parallax({
        scalarX: 50,
        scalarY: 50,
    });

    function page_ah_cursor() {
        document.body.append(...["cursor-outer", "cursor-inner"].map(className => Object.assign(document.createElement("div"), { className: `mouse-cursor ${className}` })));

        const myCursor = jQuery(".mouse-cursor");

        if (myCursor.length) {
            const cursorInner = document.querySelector(".cursor-inner");
            const cursorOuter = document.querySelector(".cursor-outer");
            let mouseY, mouseX = 0;
            let isHovering = false;

            window.onmousemove = function (event) {
                if (!isHovering) {
                    cursorOuter.style.transform = `translate(${event.clientX}px, ${event.clientY}px)`;
                    cursorInner.style.transform = `translate(${event.clientX}px, ${event.clientY}px)`;
                    mouseY = event.clientY;
                    mouseX = event.clientX;
                }
            };

            $("body").on(
                "mouseenter",
                "a, button, .cursor-pointer",
                function () {
                    cursorInner.classList.add("cursor-hover");
                    cursorOuter.classList.add("cursor-hover");
                }
            );

            $("body").on(
                "mouseleave",
                "a, button, .cursor-pointer",
                function () {
                    if ($(this).is("a") || $(this).is("button") && $(this).closest(".cursor-pointer").length === 0) {
                        cursorInner.classList.remove("cursor-hover");
                        cursorOuter.classList.remove("cursor-hover");
                    }
                }
            );

            cursorInner.style.visibility = "visible";
            cursorOuter.style.visibility = "visible";
        }
    }
    if ($('.body-2').length > 0) {
        page_ah_cursor();
    }

    $('#contact-us__form').submit(function(event) {
        event.preventDefault();
        var form = $(this);
        $('.loading-form').show();

        setTimeout(function() {
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize()
            }).done(function(data) {
                $('.loading-form').hide();
                $('.contact-us__form').append('<p class="success-message mt-3 mb-0">Your message has been sent successfully.</p>');
            }).fail(function(data) {
                $('.loading-form').hide();
                $('.contact-us__form').append('<p class="error-message mt-3 mb-0">Something went wrong. Please try again later.</p>');

            });
        }, 1000);
    });

    $('.contact-us-1__select select').niceSelect();


    // update javascrpt


    // carouselTicker initail 
    $('.carouselTicker-nav').carouselTicker({
    });
    $(".carouselTicker-start").carouselTicker({
        direction: "next",
        
    });


    /*testimonial__slider***/
    let testimonial__slider = new Swiper(".testimonial__slider", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        clickable: true,
        autoplay: {
            delay: 3000,
        },
        navigation: {
            prevEl: ".testimonial__slider__arrow-prev",
            nextEl: ".testimonial__slider__arrow-next",
        }
    });


    // blog-7-slide active 
    let blog7Slide = new Swiper(".blog-7__active", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: false,
        roundLengths: true,
        clickable: true,
        navigation: {
            nextEl: ".blog-7-button-arrow-next",
            prevEl: ".blog-7-button-arrow-prev",
        },
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1400: {
                slidesPerView: 3,
            },
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

    // feedback-7-slide active 
    let feedback = new Swiper(".feedback-7__active", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: false,
        centerMode: true,
        clickable: true,
        autoplay: {
            delay: 3000,
        },
        breakpoints: {
            1400: {
                slidesPerView: 3,
            },
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

       /*feedback-5__active***/
       let Feedback__active = new Swiper(".feedback-5__active", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        pagination: {
            el: ".customer-feedback__dots",
            clickable: true,
        },
        breakpoints: {
            1200: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });

        /*feedback-6__active***/
        let Feedback6__active = new Swiper(".feedback-6__active", {
            slidesPerView: 2,
            spaceBetween: 30,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
            },
            navigation: {
                prevEl: ".feedback-6__button__arrow__prev",
                nextEl: ".feedback-6__button__arrow__next",
            },
            breakpoints: {
                992: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 1,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        });

           /*blog-6__active***/
    let blog6__active = new Swiper(".blog-6__active", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        centeredSlides: false,
        autoplay: {
            delay: 3000,
        },
        navigation: {
            prevEl: ".blog-6__button__arrow__prev",
            nextEl: ".blog-6__button__arrow__next",
        },
        breakpoints: {
            1200: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 2,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });


    // pricingSwitch-button

    // pricing-5__active ----------------------------------
    const pricingMonthlyBtn = $("#Monthly"),
        pricingYearlyBtn = $("#Annually"),
        pricingValues = $(".pricing-5__card__price h2, .yearly p");

    if (pricingMonthlyBtn[0] && pricingYearlyBtn[0] && pricingValues.length > 0) {
        pricingMonthlyBtn[0].addEventListener("click", function () {
            updatePricingValuestop("monthly");
            pricingYearlyBtn[0].classList.remove("active");
            pricingMonthlyBtn[0].classList.add("active");
        });

        pricingYearlyBtn[0].addEventListener("click", function () {
            updatePricingValuestop("yearly");
            pricingMonthlyBtn[0].classList.remove("active");
            pricingYearlyBtn[0].classList.add("active");
        });
    }

    function updatePricingValuestop(option) {
        pricingValues.each(function () {
            const pricingValue = $(this);
            const yearlyValue = pricingValue.attr("data-Annually");
            const monthlyValue = pricingValue.attr("data-Monthly");

            const newValue = option === "monthly" ? monthlyValue : yearlyValue;
            pricingValue.html(newValue);
        });
    }

    // --------------------------------------------------------------------------------------------
    const pricingSwitch = $("#checkbox"),
        pricingValues1 = $(".pricing-5__card__price h2");

    if (pricingSwitch[0] && pricingValues1.length > 0) {
        pricingSwitch[0].addEventListener("change", function () {
            if (pricingSwitch[0].checked) {
                updatePricingValues("yearly");
                $("#Annually").addClass("active");
                $("#Monthly").removeClass("active");
            } else {
                updatePricingValues("monthly");
                $("#Monthly").addClass("active");
                $("#Annually").removeClass("active");
            }
        });
    }

    function updatePricingValues(option) {
        pricingValues1.each(function () {
            const pricingValue = $(this);
            const yearlyValue = pricingValue.attr("data-Annually");
            const monthlyValue = pricingValue.attr("data-Monthly");

            const newValue = option === "monthly" ? monthlyValue : yearlyValue;
            pricingValue.html(newValue);
        });
    }

    const pricingMonthlyBtn6 = $("#Monthly"),
        pricingYearlyBtn6 = $("#Annually"),
        pricingValues_6 = $(".pricing-6__card__price h2, .yearly p");

    if (pricingMonthlyBtn6[0] && pricingYearlyBtn6[0] && pricingValues_6.length > 0) {
        pricingMonthlyBtn6[0].addEventListener("click", function () {
            updatePricingValues_6top("monthly");
            pricingYearlyBtn6[0].classList.remove("active");
            pricingMonthlyBtn6[0].classList.add("active");
        });

        pricingYearlyBtn6[0].addEventListener("click", function () {
            updatePricingValues_6top("yearly");
            pricingMonthlyBtn6[0].classList.remove("active");
            pricingYearlyBtn6[0].classList.add("active");
        });
    }

    function updatePricingValues_6top(option) {
        pricingValues_6.each(function () {
            const pricingValue = $(this);
            const yearlyValue = pricingValue.attr("data-Annually");
            const monthlyValue = pricingValue.attr("data-Monthly");

            const newValue = option === "monthly" ? monthlyValue : yearlyValue;
            pricingValue.html(newValue);
        });
    }
    // --------------------------------------------------------------------------------------------
    const pricingSwitch_6 = $("#checkbox"),
        pricingValues_61 = $(".pricing-6__card__price h2");

    if (pricingSwitch_6[0] && pricingValues_61.length > 0) {
        pricingSwitch_6[0].addEventListener("change", function () {
            if (pricingSwitch_6[0].checked) {
                updatePricingValues_6("yearly");
                $("#Annually").addClass("active");
                $("#Monthly").removeClass("active");
            } else {
                updatePricingValues_6("monthly");
                $("#Monthly").addClass("active");
                $("#Annually").removeClass("active");
            }
        });
    }

    function updatePricingValues_6(option) {
        pricingValues_61.each(function () {
            const pricingValue = $(this);
            const yearlyValue = pricingValue.attr("data-Annually");
            const monthlyValue = pricingValue.attr("data-Monthly");

            const newValue = option === "monthly" ? monthlyValue : yearlyValue;
            pricingValue.html(newValue);
        });
    }


})(jQuery);
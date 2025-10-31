<header>
  <div id="header-sticky" class="header__area header-1">
    <div class="container">
      <div class="mega__menu-wrapper p-relative">
        <div class="header__main">
          <div class="header__left d-flex align-items-center">
            <div class="header__logo">
              <a href="{{route('site.index')}}">
                <div class="logo">
                  <img src="{{asset('assets/imgs/logo/auth-login-illustration-light.png')}}" alt="ideadocs">
                </div>
              </a>
            </div>

            <div class="horizontal-bar"></div>

            <div class="mean__menu-wrapper d-none d-lg-block">
              <div class="main-menu onepagenav">
                <nav id="mobile-menu">
                  <ul>
                    <li class="active"><a href="#banner">Anasayfa</a></li>
                    <li><a href="#features">Özellikler</a></li>
                    <li><a href="#work-step">Nasıl Çalışır ?</a></li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>

          <div class="header__right">
            <div class="header__action d-flex align-items-center">
              <div class="header__btn-wrap d-none d-sm-inline-flex">
                <a href="{{route('dashboard')}}" class="rr-btn__login">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="15" viewBox="0 0 13 15" fill="none">
                    <path d="M6.15454 7C7.8114 7 9.15454 5.65685 9.15454 4C9.15454 2.34315 7.8114 1 6.15454 1C4.49769 1 3.15454 2.34315 3.15454 4C3.15454 5.65685 4.49769 7 6.15454 7Z" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.308 14.2C11.308 11.878 8.998 10 6.154 10C3.31 10 1 11.878 1 14.2" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg> Giriş Yap
                </a>

{{--                <a href="#" class="rr-btn rr-btn__theme">--}}
{{--                                    <span class="btn-wrap">--}}
{{--                                        <span class="text-one">Sign up <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none">--}}
{{--                                              <path d="M1.12295 8.87947C0.97392 8.73337 0.960372 8.50475 1.08231 8.34364L1.12295 8.29749L4.48671 5L1.12295 1.70251C0.97392 1.55641 0.960372 1.32779 1.08231 1.16669L1.12295 1.12053C1.27198 0.974432 1.50519 0.961151 1.66952 1.08069L1.7166 1.12053L5.37705 4.70901C5.52608 4.85511 5.53963 5.08373 5.4177 5.24483L5.37705 5.29099L1.7166 8.87947C1.55267 9.04018 1.28688 9.04018 1.12295 8.87947Z" fill="white" stroke="white" stroke-width="0.5"/>--}}
{{--                                            </svg></span>--}}
{{--                                        <span class="text-two">Sign up <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none">--}}
{{--                                          <path d="M1.12295 8.87947C0.97392 8.73337 0.960372 8.50475 1.08231 8.34364L1.12295 8.29749L4.48671 5L1.12295 1.70251C0.97392 1.55641 0.960372 1.32779 1.08231 1.16669L1.12295 1.12053C1.27198 0.974432 1.50519 0.961151 1.66952 1.08069L1.7166 1.12053L5.37705 4.70901C5.52608 4.85511 5.53963 5.08373 5.4177 5.24483L5.37705 5.29099L1.7166 8.87947C1.55267 9.04018 1.28688 9.04018 1.12295 8.87947Z" fill="white" stroke="white" stroke-width="0.5"/>--}}
{{--                                        </svg></span>--}}
{{--                                    </span>--}}
{{--                </a>--}}
              </div>

              <div class="header__hamburger ml-20 d-lg-none">
                <div class="sidebar__toggle">
                  <a class="bar-icon" href="avascript:void(0)">
                    <span></span>
                    <span></span>
                    <span></span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

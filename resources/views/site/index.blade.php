@extends('site.site-master')
@section('title','İdeaDocs')
@section('content')

  <!-- Banner area start -->
  <section id="banner" class="banner banner__space overflow-hidden theme-bg-1 parallax-element">
    <div class="container container-xxl">
      <div class="banner__shape">
        <div class="banner__shape-container">
          <svg width="1920" height="870" viewBox="0 0 1920 870" fill="none" xmlns="http://www.w3.org/2000/svg">
            <mask id="mask0_486_59" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="1920" height="870">
              <rect width="1920" height="870" fill="#D9D9D9"/>
            </mask>
            <g mask="url(#mask0_486_59)">
              <g class="layer" data-depth="0.09">
                <circle cx="1737" cy="69" r="6" fill="#FBD95E"/>
              </g>
              <g class="layer" data-depth="0.03">
                <circle cx="1781" cy="95" r="4" fill="#5489FA"/>
              </g>
              <g class="layer" data-depth="0.03">
                <circle cx="1749" cy="114" r="2" fill="#F461A6"/>
              </g>

              <g class="layer" data-depth="0.03">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M910.895 539.321C909.574 540.245 908.38 541.336 907.343 542.565C911.58 542.172 916.962 542.814 923.21 545.938C929.883 549.275 935.256 549.387 938.995 548.666C938.656 547.639 938.229 546.652 937.723 545.713C933.442 546.164 927.954 545.567 921.559 542.369C917.498 540.339 913.918 539.502 910.895 539.321ZM935.222 542.154C931.842 538.377 926.929 536 921.461 536C919.856 536 918.298 536.205 916.813 536.59C918.824 537.139 920.961 537.942 923.21 539.067C927.893 541.408 931.935 542.162 935.222 542.154ZM939.795 552.272C935.277 553.164 929.039 552.981 921.559 549.241C914.566 545.744 909 545.789 905.247 546.623C905.053 546.666 904.864 546.711 904.679 546.758C904.188 547.827 903.795 548.951 903.514 550.118C903.815 550.038 904.125 549.961 904.446 549.89C909 548.878 915.434 548.922 923.21 552.81C930.203 556.307 935.769 556.263 939.523 555.428C939.651 555.4 939.778 555.37 939.903 555.34C939.916 555.049 939.923 554.756 939.923 554.461C939.923 553.721 939.879 552.99 939.795 552.272ZM939.3 559.236C934.826 560.018 928.769 559.718 921.559 556.113C914.566 552.616 909 552.66 905.247 553.494C904.413 553.68 903.664 553.905 903.003 554.143C903.001 554.249 903 554.355 903 554.461C903 564.658 911.266 572.923 921.461 572.923C930.006 572.923 937.196 567.118 939.3 559.236Z" fill="#6640FF"/>
              </g>
              <path d="M1202 83.8821C1195.78 88.9099 1189.86 88.2414 1185.53 87.7578C1181.65 87.324 1179.83 87.2102 1177.37 89.2014C1174.9 91.1926 1174.61 93.006 1174.19 96.9244C1173.71 101.284 1173.06 107.257 1166.84 112.285C1160.62 117.313 1154.7 116.644 1150.38 116.161C1146.5 115.727 1144.68 115.613 1142.21 117.604C1139.75 119.595 1139.45 121.409 1139.03 125.32C1138.56 129.679 1137.91 135.653 1131.69 140.681C1125.47 145.708 1119.54 145.04 1115.22 144.556C1111.34 144.123 1109.52 144.009 1107.05 146L1100 137.118C1106.22 132.09 1112.14 132.759 1116.47 133.242C1120.35 133.676 1122.17 133.79 1124.63 131.799C1127.1 129.807 1127.39 127.994 1127.81 124.083C1128.29 119.723 1128.94 113.75 1135.16 108.722C1141.38 103.694 1147.3 104.363 1151.62 104.846C1155.5 105.28 1157.32 105.394 1159.79 103.403C1162.26 101.412 1162.55 99.5983 1162.97 95.6799C1163.44 91.3206 1164.09 85.3471 1170.31 80.3193C1176.53 75.2916 1182.46 75.96 1186.78 76.4436C1190.66 76.8774 1192.48 76.9912 1194.95 75L1202 83.8821Z" fill="#EFECFF"/>
              <g class="layer" data-depth="0.01">
                <path d="M1194.45 75.3538C1196.41 77.782 1196.04 81.4621 1193.15 82.6205C1188.76 84.3778 1184.71 83.9287 1181.53 83.5781C1177.65 83.1504 1175.83 83.0383 1173.37 85.0014C1170.9 86.9645 1170.61 88.7524 1170.19 92.6156C1169.71 96.9135 1169.06 102.803 1162.84 107.76C1156.62 112.717 1150.7 112.058 1146.38 111.581C1142.5 111.153 1140.68 111.041 1138.21 113.004C1135.75 114.967 1135.45 116.755 1135.03 120.611C1134.56 124.909 1133.91 130.799 1127.69 135.756C1121.47 140.713 1115.54 140.053 1111.22 139.577C1110.07 139.45 1109.1 139.351 1108.24 139.325C1105.15 139.234 1101.49 139.055 1099.55 136.646V136.646C1097.59 134.218 1097.96 130.538 1100.85 129.38C1105.24 127.622 1109.29 128.071 1112.47 128.422C1116.35 128.85 1118.17 128.962 1120.63 126.999C1123.1 125.035 1123.39 123.248 1123.81 119.391C1124.29 115.094 1124.94 109.204 1131.16 104.247C1137.38 99.2903 1143.3 99.9493 1147.62 100.426C1151.5 100.854 1153.32 100.966 1155.79 99.0028C1158.26 97.0397 1158.55 95.2518 1158.97 91.3886C1159.44 87.0907 1160.09 81.2013 1166.31 76.2444C1172.53 71.2875 1178.46 71.9465 1182.78 72.4233C1183.93 72.5502 1184.9 72.6493 1185.76 72.6747C1188.85 72.7659 1192.51 72.945 1194.45 75.3538V75.3538Z" fill="url(#paint0_linear_486_59)" fill-opacity="0.5"/>
              </g>
            </g>
            <defs>
              <linearGradient id="paint0_linear_486_59" x1="1147" y1="71" x2="1147" y2="141" gradientUnits="userSpaceOnUse">
                <stop stop-color="#9888F4" offset="0%"/>
                <stop offset="1" stop-color="#907EF3"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
        <div class="banner__shape-ball"><div class="zooming"><div class="layer" data-depth="0.02"><img class="wow fadeIn animated" data-wow-delay=".7s" src="{{asset('assets/imgs/banner/ball-shape.svg')}}" alt="image not found"></div></div></div>
      </div>
      <div class="banner__image wow fadeIn animated" data-wow-delay=".5s">
        <div class="rightLeft"><img src="{{asset('assets/imgs/banner/banner.png')}}" alt="image not found"></div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="banner__content">
            <span class="banner__sub-title mb-20 mb-xs-10 wow fadeIn animated" data-wow-delay=".1s">Döküman Yükleme Sistemi<img class="rightLeft" src="{{asset('assets/imgs/icons/arrow-right.svg')}}" alt="arrow not found"></span>
            <h1 class="banner__title mb-15 mb-xs-5 wow fadeIn animated" data-wow-delay=".3s">Belgelerinizi Güvenle Yükleyin ve Yönetin </h1>
            <p class="mb-40 mb-xs-30 wow fadeIn animated" data-wow-delay=".5s">İdea belgelerinizi hızlı ve güvenli bir şekilde yükleyip yönetmenizi sağlayan yenilikçi bir sistemdir. Bağımsız Denetim Sürecinde belgelerinizi güvenle yükleyip yönetebilirsiniz.</p>

          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Banner area end -->

  <!--our-features start-->
  <section id="features" class="our-features section-space__features overflow-hidden parallax-element">
    <div class="container">
      <div class="section-shape">
        <div class="bar-shape" data-parallax='{"y": -200, "x": 300, "smoothness": 15}'>
          <svg width="1110" height="1309" viewBox="0 0 1110 1309" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g>
              <rect opacity="0.1" x="925.229" y="472" width="205.367" height="977.546" rx="102.683" transform="rotate(45 925.229 472)" fill="#403CFA"/>
            </g>
            <g>
              <rect opacity="0.1" x="964.229" y="78" width="205.367" height="977.546" rx="102.683" transform="rotate(45 964.229 78)" fill="#403CFA"/>
            </g>
            <g data-parallax='{"y": -100, "x": 100, "smoothness": 15}'>
              <rect opacity="0.1" x="691.229" width="205.367" height="977.546" rx="102.683" transform="rotate(45 691.229 0)" fill="#403CFA"/>
            </g>
          </svg>
        </div>

        <div class="small-ball-shape" data-parallax='{"y": -100, "x": -100, "smoothness": 15}'>
          <svg width="1920" height="897" viewBox="0 0 1920 897" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g data-parallax='{"x": 200, "smoothness": 15}'>
              <g class="leftRight">
                <path d="M1769 713.884C1776.74 720.044 1784.12 719.225 1789.5 718.633C1794.33 718.101 1796.6 717.962 1799.67 720.402C1802.74 722.842 1803.11 725.064 1803.63 729.865C1804.22 735.207 1805.03 742.526 1812.77 748.687C1820.52 754.848 1827.89 754.029 1833.28 753.436C1838.11 752.905 1840.37 752.765 1843.45 755.205C1846.51 757.645 1846.88 759.867 1847.41 764.66C1847.99 770.002 1848.8 777.321 1856.55 783.482C1864.29 789.643 1871.67 788.824 1877.05 788.231C1881.88 787.7 1884.15 787.56 1887.22 790L1896 779.116C1888.26 772.956 1880.88 773.775 1875.5 774.367C1870.67 774.899 1868.4 775.038 1865.33 772.598C1862.26 770.158 1861.89 767.936 1861.37 763.144C1860.78 757.802 1859.97 750.482 1852.23 744.322C1844.48 738.161 1837.11 738.98 1831.72 739.572C1826.89 740.104 1824.63 740.243 1821.55 737.803C1818.48 735.364 1818.12 733.142 1817.59 728.34C1817.01 722.999 1816.2 715.679 1808.45 709.518C1800.71 703.357 1793.33 704.176 1787.95 704.769C1783.12 705.3 1780.85 705.44 1777.78 703L1769 713.884Z" fill="#FEEAA6" fill-opacity="0.4"/>
              </g>
            </g>
            <g class="rightLeft">
              <path d="M1778.35 702.444C1775.95 705.449 1776.4 709.99 1779.97 711.433C1785.4 713.628 1790.41 713.069 1794.34 712.633C1799.13 712.101 1801.38 711.962 1804.43 714.402C1807.47 716.842 1807.84 719.064 1808.36 723.865C1808.94 729.207 1809.74 736.526 1817.43 742.687C1825.11 748.848 1832.43 748.029 1837.77 747.436C1842.56 746.905 1844.81 746.765 1847.86 749.205C1850.9 751.645 1851.27 753.867 1851.79 758.66C1852.37 764.002 1853.17 771.321 1860.86 777.482C1868.54 783.643 1875.86 782.824 1881.2 782.231C1882.59 782.076 1883.77 781.955 1884.82 781.921C1888.67 781.794 1893.24 781.563 1895.65 778.556C1898.05 775.551 1897.6 771.01 1894.03 769.567C1888.6 767.372 1883.59 767.931 1879.66 768.367C1874.87 768.899 1872.62 769.038 1869.57 766.598C1866.53 764.158 1866.16 761.936 1865.64 757.144C1865.06 751.802 1864.26 744.482 1856.57 738.322C1848.89 732.161 1841.57 732.98 1836.23 733.572C1831.44 734.104 1829.19 734.243 1826.14 731.803C1823.09 729.364 1822.73 727.142 1822.21 722.34C1821.63 716.999 1820.83 709.679 1813.14 703.518C1805.46 697.357 1798.14 698.176 1792.8 698.769C1791.41 698.924 1790.23 699.045 1789.18 699.079C1785.33 699.206 1780.76 699.437 1778.35 702.444Z" fill="url(#paint0_linear_431_74)" fill-opacity="0.5"/>
            </g>
            <g data-parallax='{"x": 250, "smoothness": 15}'>
              <g class="leftRight">
                <circle cx="1364" cy="96" r="6" fill="#6640FF"/>
              </g>
            </g>

            <g data-parallax='{"x": 150, "smoothness": 15}'>
              <g class="rightLeft">
                <circle cx="1810" cy="207" r="8" fill="#6640FF"/>
              </g>
            </g>

            <g data-parallax='{"x": 200, "smoothness": 15}'>
              <g class="rightLeft">
                <path d="M402.9 98.0701C394.08 105.14 385.68 104.2 379.55 103.52C374.05 102.91 371.47 102.75 367.97 105.55C364.48 108.35 364.06 110.9 363.46 116.41C362.79 122.54 361.87 130.94 353.05 138.01C344.23 145.08 335.83 144.14 329.7 143.46C324.2 142.85 321.62 142.69 318.12 145.49C314.63 148.29 314.21 150.84 313.61 156.34C312.94 162.47 312.02 170.87 303.2 177.94C294.38 185.01 285.98 184.07 279.85 183.39C274.35 182.78 271.77 182.62 268.27 185.42L258.27 172.93C267.09 165.86 275.49 166.8 281.62 167.48C287.12 168.09 289.7 168.25 293.2 165.45C296.69 162.65 297.11 160.1 297.71 154.6C298.38 148.47 299.3 140.07 308.12 133C316.94 125.93 325.34 126.87 331.47 127.55C336.97 128.16 339.55 128.32 343.05 125.52C346.55 122.72 346.96 120.17 347.56 114.66C348.23 108.53 349.15 100.13 357.97 93.0601C366.79 85.9901 375.19 86.9301 381.32 87.6101C386.82 88.2201 389.4 88.3801 392.9 85.5801L402.9 98.0701Z" fill="#EFECFF"/>
              </g>
              <g class="leftRight">
                <path d="M392.63 85.2456C395.392 88.6949 394.873 93.9082 390.776 95.5631C384.539 98.0824 378.794 97.4407 374.28 96.94C368.78 96.33 366.2 96.17 362.7 98.97C359.21 101.77 358.79 104.32 358.19 109.83C357.52 115.96 356.6 124.36 347.78 131.43C338.96 138.5 330.56 137.56 324.43 136.88C318.93 136.27 316.35 136.11 312.85 138.91C309.36 141.71 308.94 144.26 308.34 149.76C307.67 155.89 306.75 164.29 297.93 171.36C289.11 178.43 280.71 177.49 274.58 176.81C272.978 176.632 271.624 176.493 270.422 176.454C266.006 176.309 260.762 176.044 258 172.594C255.238 169.145 255.757 163.932 259.854 162.277C266.091 159.758 271.836 160.399 276.35 160.9C281.85 161.51 284.43 161.67 287.93 158.87C291.42 156.07 291.84 153.52 292.44 148.02C293.11 141.89 294.03 133.49 302.85 126.42C311.67 119.35 320.07 120.29 326.2 120.97C331.7 121.58 334.28 121.74 337.78 118.94C341.28 116.14 341.69 113.59 342.29 108.08C342.96 101.95 343.88 93.55 352.7 86.48C361.52 79.41 369.92 80.35 376.05 81.03C377.652 81.2077 379.006 81.3472 380.208 81.3865C384.624 81.5309 389.868 81.7958 392.63 85.2456Z" fill="url(#paint1_linear_431_74)" fill-opacity="0.5"/>
              </g>
            </g>
            <defs>
              <linearGradient id="paint0_linear_431_74" x1="1837" y1="697" x2="1837" y2="784" gradientUnits="userSpaceOnUse">
                <stop stop-color="#FFE176" offset="0%"/>
                <stop offset="1" stop-color="#FFD646"/>
              </linearGradient>
              <linearGradient id="paint1_linear_431_74" x1="325.315" y1="79" x2="325.315" y2="178.84" gradientUnits="userSpaceOnUse">
                <stop stop-color="#9888F4" offset="0%"/>
                <stop offset="1" stop-color="#907EF3"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>

      <div class="row flex-column-reverse flex-xl-row">
        <div class="col-xl-5">
          <div class="our-features__media wow fadeIn animated" data-wow-delay=".3s">
            <img src="{{asset('assets/imgs/our-feature/our-feature-screen.png')}}" alt="image not found">
          </div>
        </div>
        <div class="col-xl-7">
          <div class="section__title-wrapper mb-60 mb-sm-50 mb-xs-40">
            <h2 class="section__title mb-5 text-uppercase wow fadeIn animated" data-wow-delay=".3s">Kullanışlı ve Pratik Panel</h2>
            <p class="mb-0 wow fadeIn animated" data-wow-delay=".5s">Kullanıcı dostu bir arayüzle tasarlanan İdeaDocs, belgelerinizi kolayca yüklemenizi, düzenlemenizi ve kategorilere ayırmanızı sağlar. Gelişmiş işlevlerle dolu bu pratik panel, işlemlerinizi hızlandırır ve yönetimi zahmetsiz hale getirir.</p>
          </div>

          <div class="our-features__item-wrapper d-grid">
            <div class="our-features__item wow fadeIn animated" data-wow-delay=".7s">
              <div class="our-features__item-header d-flex align-items-center mb-15 mb-xs-10">
                <div class="icon">
                  <img src="{{asset('assets/imgs/our-feature/our-features__item-1.svg')}}" alt="icon not found">
                </div>
                <h4>Hızlı ve Güvenli Yükleme</h4>
              </div>
            </div>

            <div class="our-features__item wow fadeIn animated" data-wow-delay=".9s">
              <div class="our-features__item-header d-flex align-items-center mb-15 mb-xs-10">
                <div class="icon">
                  <img src="{{asset('assets/imgs/our-feature/our-features__item-2.svg')}}" alt="icon not found">
                </div>
                <h4>Kategorilere Göre Yönetim</h4>
              </div>
            </div>

            <div class="our-features__item wow fadeIn animated" data-wow-delay="1.1s">
              <div class="our-features__item-header d-flex align-items-center mb-15 mb-xs-10">
                <div class="icon">
                  <img src="{{asset('assets/imgs/our-feature/our-features__item-3.svg')}}" alt="icon not found">
                </div>
                <h4>Basit ve Pratik Kullanım</h4>
              </div>
            </div>

            <div class="our-features__item wow fadeIn animated" data-wow-delay="1.3s">
              <div class="our-features__item-header d-flex align-items-center mb-15 mb-xs-10">
                <div class="icon">
                  <img src="{{asset('assets/imgs/our-feature/our-features__item-4.svg')}}" alt="icon not found">
                </div>
                <h4>Belgelerinizi Güvende Tutun</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--our-features end-->

  <!--work-step start-->
  <section id="work-step" class="work-step section-space theme-bg-1 overflow-hidden parallax-element">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section__title-wrapper text-center mb-60 mb-sm-50 mb-xs-40">
            <span class="section__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s"><span class="layer" data-depth="0.009">#3</span> Kolay & Hızlı <img class="rightLeft" src="{{asset('assets/imgs/icons/arrow-right.svg')}}" alt="arrow not found"></span>
            <h2 class="section__title text-uppercase wow fadeIn animated" data-wow-delay=".3s">3 Adımda Kullanım</h2>
          </div>

          <div class="work-step__item-wrapper d-flex flex-column flex-xl-row justify-content-between">
            <div class="work-step__item d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between wow fadeIn animated" data-wow-delay=".5s">
              <div class="work-step__item-left">
                <div class="work-step__item__header mb-25 d-flex align-items-center justify-content-between">
                  <div class="work-step__item-icon">
                    <img src="{{asset('assets/imgs/work-step/work-step__item-1.png')}}" alt="image not found">
                  </div>

                  <button class="work-step__item-step">Adım 1</button>
                </div>

                <div class="work-step__item__body">
                  <h4 class="work-step__item-title mb-15">Giriş Yapın</h4>
                  <p class="mb-0">Kullanıcı adı ve şifrenizle giriş yapın</p>
                </div>
              </div>

              <div class="work-step__item-right">
                                <span class="readmore">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 7H13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 1L13 7L7 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
              </div>
            </div>

            <div class="work-step__item d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between wow fadeIn animated" data-wow-delay=".6s">
              <div class="work-step__item-left">
                <div class="work-step__item__header mb-25 d-flex align-items-center justify-content-between">
                  <div class="work-step__item-icon">
                    <img src="{{asset('assets/imgs/work-step/work-step__item-2.png')}}" alt="image not found">
                  </div>

                  <button class="work-step__item-step">Adım 2</button>
                </div>

                <div class="work-step__item__body">
                  <h4 class="work-step__item-title mb-15">Dosyalarınızı yükleyin</h4>
                  <p class="mb-0">Evraklarınızı güvenle yükleyin</p>
                </div>
              </div>

              <div class="work-step__item-right">
                                <span class="readmore">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 7H13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 1L13 7L7 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
              </div>
            </div>

            <div class="work-step__item d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between wow fadeIn animated" data-wow-delay=".7s">
              <div class="work-step__item-left">
                <div class="work-step__item__header mb-25 d-flex align-items-center justify-content-between">
                  <div class="work-step__item-icon">
                    <img src="{{asset('assets/imgs/work-step/work-step__item-3.png')}}" alt="image not found">
                  </div>

                  <button class="work-step__item-step">Adım 3</button>
                </div>

                <div class="work-step__item__body">
                  <h4 class="work-step__item-title mb-15">Durum Takibi</h4>
                  <p class="mb-0">Evraklarınızın onay durumunu takip edin.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--work-step end-->



@endsection

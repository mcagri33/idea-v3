@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $categories = \App\Models\DocumentCategory::orderBy('order', 'asc')->get();

@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  @if(!isset($navbarFull))
    <div class="app-brand demo">
      <a href="{{url('/panel/')}}" class="app-brand-link">
      <span class="app-brand-logo demo">
        @include('_partials.macros',["height"=>20])
      </span>
        <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
        <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
      </a>
    </div>
  @endif

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ $menu->menuHeader }}</span>
        </li>
      @else
        @php
          $activeClass = '';
          $currentRouteName = Route::currentRouteName();

          if ($currentRouteName === $menu->slug) {
              $activeClass = 'active'; // for exact match
          } elseif (isset($menu->submenu)) {
              // Check for active submenus
              foreach ($menu->submenu as $submenu) {
                  if (str_contains($currentRouteName, $submenu->slug)) {
                      $activeClass = 'active open'; // if a submenu is active
                      break;
                  }
              }
          }

          $hasPermission = false;
          if (isset($menu->slug)) {
              $permissions = is_array($menu->slug) ? $menu->slug : [$menu->slug];
              foreach ($permissions as $permission) {
                  if (auth()->user()->can($permission)) {
                      $hasPermission = true;
                      break;
                  }
              }
          }
        @endphp

        {{-- main menu --}}
        @if ($hasPermission)
          <li class="menu-item {{ $activeClass }}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
              @isset($menu->icon)
                <i class="{{ $menu->icon }}"></i>
              @endisset
             <div style="white-space: normal; word-break: break-word; overflow-wrap: break-word;">
  				{{ isset($menu->name) ? __($menu->name) : '' }}
			</div>

              @isset($menu->badge)
                <div class="badge bg-label-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
              @endisset
            </a>
            {{-- submenu --}}
            @isset($menu->submenu)
              @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
            @endisset
          </li>
        @endif
      @endif
    @endforeach

    @foreach ($categories as $category)
      @php
        $hasCategoryPermission = auth()->user()->can('view-category', $category);
         $currentUrl = url()->current();
         $categoryUrl = url('/panel/category/' . $category->slug);
         $categoryActiveClass = $currentUrl === $categoryUrl ? 'active' : '';
      @endphp
      @if ($hasCategoryPermission)
          <li class="menu-item {{ $categoryActiveClass }}">
          <a href="{{ url('/panel/category/' . $category->slug) }}" class="menu-link">
            <i class="menu-icon ti ti-category"></i>
            <div style="white-space: normal; word-break: break-word; overflow-wrap: break-word;">
  				{{ $category->name }}
				</div>

          </a>
        </li>
      @endif
    @endforeach
  </ul>
</aside>

<nav class="navbar border-b" role="navigation" aria-label="main navigation">
  <div class="container">
    <div class="navbar-brand">
      <a class="navbar-item" href="/">
        <img src="/assets/img/SneakerSouqq-Logo-Black.png" alt="" />
      </a>
  
      <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>
  
    <div id="navbarBasicExample" class="navbar-menu">
      <div class="navbar-start">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
          {{ __('Dashboard') }}
        </x-nav-link>

        <x-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.index')">
          {{ __('Agents') }}
        </x-nav-link>

        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">
          {{ __('Product CSVs') }}
        </x-nav-link>
      </div>
  
      <div class="navbar-end">
        <div class="navbar-item has-dropdown is-hoverable">
          <a class="navbar-link">
            {{ Auth::user()->name }}
          </a>
  
          <div class="navbar-dropdown is-right">
            <x-nav-link :href="route('profile.edit')">
              {{ __('Profile') }}
            </x-nav-link>
            
            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
              @csrf

              <x-nav-link :href="route('logout')"
                      onclick="event.preventDefault();
                                  this.closest('form').submit();">
                  {{ __('Log Out') }}
              </x-nav-link>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
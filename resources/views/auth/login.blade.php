<x-guest-layout>
  <!-- Session Status -->
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="field">
      <label class="label">Email</label>
      <div class="control">
        <input class="input" id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
      </div>
    </div>

    <div class="field">
      <label class="label">Password</label>
      <div class="control">
        <input class="input" id="password" name="password" type="password" :value="old('password')" required autofocus autocomplete="current-password">
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
      </div>
    </div>

    <div class="field">
      <div class="control">
        <label class="checkbox" for="remember_me">
          <input id="remember_me" type="checkbox" name="remember">
          {{ __('Remember me') }}
        </label>
      </div>
    </div>

    <div class="is-align-items-center is-flex mt-4">
      <div class="field m-0 mb-0 mr-3">
        <div class="control">
          <button class="button is-link" type="submit">
            {{ __('Log in') }}
          </button>
        </div>
      </div>

      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">
          {{ __('Forgot your password?') }}
        </a>
      @endif
    </div>
  </form>
</x-guest-layout>

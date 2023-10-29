<x-guest-layout>
  <p class="mb-3">
    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
  </p>

  <!-- Session Status -->
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="field">
      <label class="label">Email</label>
      <div class="control">
        <input class="input" id="email" name="email" type="email" :value="old('email')" required autofocus>
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
      </div>
    </div>

    <div class="is-align-items-center is-flex mt-4">
      <div class="field m-0 mb-0 mr-3">
        <div class="control">
          <button class="button is-link" type="submit">
            {{ __('Email Password Reset Link') }}
          </button>
        </div>
      </div>

      @if (Route::has('password.request'))
        <a href="{{ route('login') }}">
          {{ __('Back to login') }}
        </a>
      @endif
    </div>

  </form>
</x-guest-layout>

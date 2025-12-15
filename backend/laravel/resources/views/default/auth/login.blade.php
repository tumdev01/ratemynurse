@extends('layouts.app')

@section('content')
<section class="bg-gray-50">
  <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
      <a href="{{ url('/') }}" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
          <img class="w-90 h-90 mr-2" src="{{ asset('images/main-logo.png') }}" alt="logo">
      </a>
      <div class="w-full bg-white rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
          <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
              <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                  Sign in to your account
              </h1>

              {{-- แสดง Error Validation --}}
              @if ($errors->any())
                  <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                      <ul class="list-disc list-inside">
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

              <form method="POST" action="{{ route('login') }}" class="space-y-4 md:space-y-6" novalidate>
                  @csrf

                  <div>
                      <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
                      <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2.5"
                      >
                  </div>

                  <div>
                      <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                      <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2.5"
                      >
                  </div>

                  <div class="flex items-center justify-between">
                      <div class="flex items-center">
                          <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-indigo-300"
                            {{ old('remember') ? 'checked' : '' }}
                          >
                          <label for="remember" class="ml-3 text-sm text-gray-500">Remember me</label>
                      </div>
                      @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:underline">Forgot password?</a>
                      @endif
                  </div>

                  <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Sign in
                  </button>

              </form>
          </div>
      </div>
  </div>
</section>
@endsection

<x-layout bodyClass="bg-gray-200">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <x-navbars.navs.guest signin='login' signup='register'></x-navbars.navs.guest>
            </div>
        </div>
    </div>

    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
             style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-auto">
                <div class="row">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">
                                        Forgot Password
                                    </h4>
                                </div>
                            </div>
                            <div class="card-body">
                                @if (session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf
                                    <div class="input-group input-group-outline mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    @error('email')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">
                                            Send Password Reset Link
                                        </button>
                                    </div>
                                </form>

                                <p class="mt-4 text-sm text-center">
                                    Remember your password?
                                    <a href="{{ route('login') }}"
                                       class="text-primary text-gradient font-weight-bold">Sign in</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-footers.guest></x-footers.guest>
        </div>
    </main>
</x-layout>

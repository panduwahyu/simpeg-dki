<x-layout bodyClass="bg-gray-200">
    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
             style="background-image: url('https://lh3.googleusercontent.com/p/AF1QipMnJxvPwegkvSUA1FNBikMw6xktCVVGK24V1_rk=s1360-w1360-h1020-rw');">
            <span class="mask bg-gradient-dark opacity-6"></span>

            <div class="container mt-5">
                <div class="row signin-margin">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Contoh</h4>
                                    <div class="row mt-3">
                                        <h6 class='text-white text-center'>
                                            <span class="font-weight-normal">Email:</span> admin@material.com
                                            <br>
                                            <span class="font-weight-normal">Password:</span> secret
                                        </h6>
                                        <div class="col-2 text-center ms-auto">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-facebook text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-2 text-center px-1">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-github text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-2 text-center me-auto">
                                            <!-- Tombol Google diarahkan ke route google.login -->
                                            <a class="btn btn-link px-3" href="{{ route('google.login') }}">
                                                <i class="fa fa-google text-white text-lg"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <form role="form" method="POST" action="{{ route('login') }}" class="text-start">
                                    @csrf
                                    @if (Session::has('status'))
                                        <div class="alert alert-success alert-dismissible text-white" role="alert">
                                            <span class="text-sm">{{ Session::get('status') }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                                    data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="admin@material.com">
                                    </div>
                                    @error('email')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" value="secret">
                                    </div>
                                    @error('password')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="form-check form-switch d-flex align-items-center my-3">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-2" for="rememberMe">Ingat saya</label>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">
                                            Masuk
                                        </button>

                                        {{-- Tombol login Google penuh --}}
                                        <a href="{{ route('google.login') }}" class="btn btn-light w-100 my-2">
                                            <i class="fa fa-google text-danger text-lg me-2"></i>
                                            Masuk dengan Google
                                        </a>
                                    </div>

                                    <p class="mt-4 text-sm text-center">
                                        Belum punya akun?
                                        <a href="{{ route('register') }}" class="text-primary text-gradient font-weight-bold">
                                            Sign up
                                        </a>
                                    </p>
                                    <p class="text-sm text-center">
                                        Lupa password?
                                        <a href="{{ route('verify') }}" class="text-primary text-gradient font-weight-bold">Ubah di sini</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-footers.guest></x-footers.guest>
        </div>
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/jquery.min.js"></script>
        <script>
            $(function () {
                var text_val = $(".input-group input").val();
                if (text_val === "") {
                    $(".input-group").removeClass('is-filled');
                } else {
                    $(".input-group").addClass('is-filled');
                }
            });
        </script>
    @endpush

</x-layout>

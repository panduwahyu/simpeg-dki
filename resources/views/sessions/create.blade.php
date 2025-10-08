<x-layout bodyClass="bg-gray-200">
    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
             style="background-image: url('/assets/img/bps.jpg'); background-position: center; background-size: cover;">
            
            {{-- Overlay gelap --}}
            <span class="mask bg-gradient-dark opacity-6"></span>

            <div class="container mt-5">
                <div class="row min-vh-100 px-8">

                    {{-- Kiri: Branding --}}
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-center"
                        style="color: white;">
                        <img src="{{ asset('assets/img/logo-ct.png') }}" alt="Logo" class="mb-3" style="width: 160px;">
                        <h1 class="fw-bold mb-2 text-white" style="font-size: 2.5rem;">SIPETRA</h1>
                        <p class="fw-semibold mb-0" style="font-size: 1.1rem;">Sistem Informasi Pegawai Terpadu</p>
                    </div>

                    {{-- Kanan: Login Card --}}
                    <div class="col-lg-6 col-md-8 col-12 d-flex align-items-center justify-content-center">
                        <div class="card z-index-0 fadeIn3 fadeInBottom w-100" style="max-width: 400px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
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

                                    {{-- Email --}}
                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="admin@material.com">
                                    </div>
                                    @error('email')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    {{-- Password --}}
                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" value="secret">
                                    </div>
                                    @error('password')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    {{-- Remember me --}}
                                    <div class="form-check form-switch d-flex align-items-center my-3">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-2" for="rememberMe">Ingat saya</label>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">
                                            Masuk
                                        </button>
                                        <a href="{{ route('google.login') }}" class="btn btn-light w-100 my-2">
                                            <i class="fa fa-google text-danger text-lg me-2"></i>
                                            Masuk dengan Google
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- row -->
            </div> <!-- container -->
        </div> <!-- page-header -->
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/jquery.min.js"></script>
        <script>
            $(function () {
                $(".input-group input").each(function(){
                    if($(this).val() === "") {
                        $(this).closest('.input-group').removeClass('is-filled');
                    } else {
                        $(this).closest('.input-group').addClass('is-filled');
                    }
                });
            });
        </script>
    @endpush
</x-layout>

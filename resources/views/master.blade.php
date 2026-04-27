<!DOCTYPE html>
<html lang="en" class="notranslate">

<head>
    @include('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Assets File --}}
    <link rel="stylesheet" href="{{ asset('dist/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/mix/app.css') }}">
    <link rel="stylesheet" href="{{asset('dist/select2/css/select2.min.css')}}">

    <script src="{{ asset('assets/mix/app.js') }}"></script>
    <script src="{{asset('dist/select2/js/select2.min.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <style>
        .custom-radio[type="radio"] { transform: scale(1.5); }
        .custom-control-label { cursor: pointer; }
        .modal-backdrop { width: 0 !important; }
    </style>

    <div id="app">
        <div class="main-wrapper">
                @if (in_array(Auth::user()?->role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales']))
                    @include('layout.sb_admin')
                @endif

                @yield('content')

                @if (in_array(Auth::user()?->role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales']))
                    @include('layout.footer')
                @endif
        </div>
    </div>

    {{-- Profile Modal --}}
    @auth
        @if (in_array(Auth::user()->role, ['super_admin', 'store_manager', 'finance', 'admin']))
            <div class="modal fade" id="updateModalProfie" tabindex="-1" aria-labelledby="updateModalProfie"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalProfie">Update Data Admin</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ url('admin/profile') }}" method="POST"
                              class="needs-validation" novalidate="" id="formUpdateProfile">
                            @csrf
                            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Email Admin</label>
                                    <input type="text" placeholder="Email Admin" class="form-control"
                                           name="email" required
                                           value="{{ Auth::user()->email }}">
                                    <div class="invalid-feedback">Masukkan Email Admin</div>
                                </div>
                                <div class="form-group">
                                    <label>Nama Admin</label>
                                    <input type="text" placeholder="Masukkan Nama Admin" class="form-control"
                                           name="name" required
                                           value="{{ Auth::user()->name }}">
                                    <div class="invalid-feedback">Masukkan Nama Admin</div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Password Baru</label>
                                    <input type="password" placeholder="Masukkan Password Baru" class="form-control"
                                           name="password">
                                    <div class="valid-feedback">Optional</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script>
                $('.edit-profile').click(function() {
                    $('#updateModalProfie').modal('show');
                });
            </script>
        @endif
    @endauth

    @stack('scripts')

    <script>
    $('.summernote').summernote({
        placeholder: 'Tulis konten di sini...',
        tabsize: 2,
        height: 220,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear', 'fontsize']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video', 'math']],
          ['view', ['fullscreen', 'codeview']],
        ]
      });

    $('#formUpdateProfile').submit(function(e) {
        e.preventDefault();
        $.ajax({
            data: $(this).serialize(),
            type: 'POST',
            url: "{{ url('admin/profile') }}",
            beforeSend: function() {
                $.LoadingOverlay("show", { image: "", fontawesome: "fa fa-cog fa-spin" });
            },
            complete: function() {
                $.LoadingOverlay("hide", { image: "", fontawesome: "fa fa-cog fa-spin" });
            },
            success: function(data) {
                swal(data.message).then(() => {
                    window.location.href = "{{ url('/') }}"
                });
            },
            error: function(err) {
                swal(err.responseJSON.message);
            }
        });
    });
    </script>
</body>
</html>

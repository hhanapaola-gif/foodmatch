<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restaurante | Iniciar sesión</title>

    @php($icon = \App\Model\BusinessSetting::where(['key' => 'fav_icon'])->first()?->value ?? '')
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/restaurant/' . $icon ?? '') }}">

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/vendor/icon-set/style.css">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/style.css">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/toastr.css">
</head>

<body>
<main id="content" role="main" class="main">
    <div class="auth-wrapper">
        <div class="auth-wrapper-left">
            <div class="auth-left-cont">
                <img width="310" src="{{ $logo }}" alt="Logo">
                <h2 class="title">Tu <span class="c1 d-block text-capitalize">Cocina</span> <strong class="text--039D55 c1 text-capitalize">Tu Comida....</strong></h2>
            </div>
        </div>

        <div class="auth-wrapper-right">
            <div class="auth-wrapper-form">
                <form class="" id="form-id" action="{{route('branch.auth.login')}}" method="post">
                    @csrf
                    <div class="auth-header">
                        <div class="mb-5">
                            <h2 class="title">Inicia sesión</h2>
                            <div>Bienvenido de nuevo</div>
                            <p class="mb-0">¿Quieres iniciar sesión como administrador?
                                <a href="{{route('admin.auth.login')}}">
                                    Iniciar sesión de admin
                                </a>
                            </p>
                            <span class="badge mt-2">( Inicio de sesión de restaurante )</span>
                        </div>
                    </div>

                    <div class="js-form-message form-group">
                        <label class="input-label" for="signinSrEmail">Tu correo electrónico</label>

                        <input type="email" class="form-control form-control-lg" name="email" id="signinSrEmail"
                               tabindex="1" placeholder="correo@ejemplo.com" aria-label="correo@ejemplo.com"
                               required data-msg="Ingresa un correo electrónico válido.">
                    </div>

                    <div class="js-form-message form-group">
                        <label class="input-label" for="signupSrPassword" tabindex="0">
                                <span class="d-flex justify-content-between align-items-center">
                                Contraseña
                                </span>
                        </label>

                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg"
                                   name="password" id="signupSrPassword" placeholder="8 o más caracteres"
                                   aria-label="8 o más caracteres" required
                                   data-msg="Tu contraseña no es válida. Intenta de nuevo."
                                   data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": "#changePassIcon"
                                        }'>
                            <div id="changePassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="termsCheckbox"
                                   name="remember">
                            <label class="custom-control-label text-muted" for="termsCheckbox">
                                Recordarme
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg btn-block btn-primary" id="signInBtn">Iniciar sesión</button>
                </form>

                @if(env('APP_MODE')=='demo')
                    <div class="border-top border-primary pt-5 mt-10">
                        <div class="row">
                            <div class="col-10">
                                <span>Correo: mainb@mainb.com</span><br>
                                <span>Contraseña: 12345678</span>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-primary px-3" id="copyButton"><i class="tio-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>


<script src="{{asset('assets/admin')}}/js/vendor.min.js"></script>

<script src="{{asset('assets/admin')}}/js/theme.min.js"></script>
<script src="{{asset('assets/admin')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif

<script>
    $(document).on('ready', function () {

        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });

        $('.js-validate').each(function () {
            $.HSCore.components.HSValidation.init($(this));
        });
    });
</script>

@if(env('APP_MODE')=='demo')
    <script>
        $('#copyButton').on('click', function() {
            copy_cred();
        });

        function copy_cred() {
            $('#signinSrEmail').val('mainb@mainb.com');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
        }
    </script>
@endif
<!-- IE Support -->
<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>
</body>
</html>

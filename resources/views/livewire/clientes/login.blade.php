<?php

use Livewire\Volt\Component;

new class extends Component {
    //


    // datos para logueo
    public $email;
    public $contrasenia;
    public $telefono;

    // dato para recuperacion de contrasenia
    public $recuclave = false;

    // dato de error

    public $error;
    public $isLoading = false;



    public function mount()
    {
        // Si ya tenemos un teléfono guardado, emitimos evento para mantenerlo
        if (!empty($this->telefono)) {
            $this->dispatch('telefono-actualizado', $this->telefono);
        }
    }




    public $emailrescue ='';
    public $message_rescue=false;
    public $enviado=false;
    public function RecuperarClave()
    {
        $this->isLoading = false;

        try {
            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
               ])->post(config('services.sistema25.base_url').'/customer/reset_password', [
                'email' => $this->emailrescue,// contrasenia a recuperar
            ]);

            if($response->successful()) {

                $data = $response->json();
                $this->message_rescue = $data['message'];

                $this->enviado=true;


            }
        }
        catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }






    public $message_login=false;
    public $datae='';
    public function Autenticar()
    {
        $this->isLoading = true;
        $login = mb_strtolower(trim((string) $this->email));
        $phone = preg_replace('/\D+/', '', (string) $this->telefono);

        // La interfaz muestra el prefijo peruano por separado. La API compara
        // el número normalizado incluyendo el código de país registrado en Odoo.
        if (strlen($phone) === 9) {
            $phone = '51'.$phone;
        }

        try {
            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
                ])->post(config('services.sistema25.base_url').'/auth/customer', [
                'login' => $login,
                'password' => $this->contrasenia,
                'phone' => $phone
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Verificar si la autenticación fue exitosa
                if ($data['status'] === 'success') {
                    // Guardar en sesión los datos del partner
                    Session::put('user_data', [
                        //'ruc' => $data['partner']['ruc'], //20100085063 -> nestor original asignado
                        'ruc' => $data['partner']['ruc'], // 20100085063 -> Probado para Ordenes Elmero compartido conmigo
                        //'ruc' => '20608673726', // 20611828323 ubicado segun ejemplo de Word de Ricardo Cuadros
                        //20608673726 con todo
                        'razon_social' => $data['partner']['razon_social'],
                        'email' => $data['partner']['email'],
                        'telefono' => $data['partner']['telefono'] ?? null,
                        'id'=>$data['partner']['id'] ?? 0,
                        'password'=>$this->contrasenia
                    ]);
                    Session::put('es_logueado', true);

                    return $this->redirect('/dashboard', navigate: true);
                }

                if($data['status'] === 'error'){
                    $this->message_login = $data['message'];

                }
            }
            else{


                // Si hubo error en la Autenticacion
                $this->datae = $response->json();
                $this->message_login = $this->datae['message'];

                //$this->Mount();
                return null;
                //return redirect()->to('/');

            }



        } catch (\Exception $e) {
            logger('Error en autenticación: ' . $e->getMessage());
            $this->error = 'Error de conexión';
            return null;
        }
        finally {
            $this->isLoading = false;
        }
    }


}; ?>

<div>
    @include('livewire.clientes.partials.login-redesign')
    @if(false)
    <div>
        {{-- Success is as dangerous as failure. --}}

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            .login-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
               /* background-color: #f0f2f5;*/
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .login-container::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0.8;
                    /*background-image: url('https://almacenes-sos.com/wp-content/uploads/2023/11/slider-almacenes.jpg');*/
                    background-image: url("{{ asset('imgcc/fondo_log.jpg') }}");
                    z-index: -1;
                background-size: cover;
            }

            .login-container::after {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0.95;
                    background-color: #a22525;
                    z-index: -2;
                background-size: cover;
            }

            .login-box {

                background: white;
                padding: 0px;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
            }

            .login-header {
                border-radius:12px 12px 0 0;
                text-align: center;
                /*margin-bottom: 30px;*/
                background: #dc2612;
                padding-left:40px;
                padding-right:40px;
            }

            .login-header h1 {
                color: #000000;
                font-size: 24px;
                margin-bottom: 10px;
            }

            .login-header p {
                color: #000000;
                font-weight: 600;
                font-size: 16px;
            }

            .input-group {
                margin-bottom: 20px;
                margin-left:40px;
                margin-right:40px;
            }

            .input-group label {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
                color: #706f6f;
                font-weight: 600;s

            }

            input {
                width: 100%;
                padding: 12px;
                border: 1px solid rgb(221, 221, 221);
                border-radius: 6px;
                font-size: 16px;
                transition: border-color 0.2s;
                color: #000000;
            }


            input:focus {
                outline: none;
                border-color: #1a73e8;
                box-shadow: 0 0 0 2px rgba(26,115,232,0.1);
            }


            button {
                width: 100%;
                padding: 12px;
                background-color: #ff1b1b;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.2s;

            }

            button:hover {
                background-color: #ff5454;


            }

            .login-footer {
                margin-top: 20px;
                text-align: center;
                font-size: 14px;
                color: #5f6368;
            }

            .login-footer a {
                color: #ffffff;
                text-decoration: none;
            }

            .login-footer a:hover {
                text-decoration: underline;
            }

            .error-message {
                color: rgba(255, 0, 0, 0.78);
                font-size: 18px;
                margin-top: 5px;
                font-weight: 600;
            }

        </style>

        <div class="login-container">


            <div class="login-box">

                <!-- seccion de logueo normal -->
                @if($recuclave == false)
                    <div>
                        <div class="login-header">
                           {{--  <h1>Bienvenido</h1>--}}
                            <img  src="{{ asset('imgcc/logos_empresas_edit.png') }}" alt="Almacenes SOS"  >
                        </div>
                        <form>

                            <p style="text-align: center; font-weight: 600; margin-top: 15px; color: #505457">INGRESE SUS CREDENCIALES</p>
                            <br>
                            <div class="input-group">
                                <label for="email">Correo</label>
                                <input type="text" id="email" wire:model="email" placeholder="Correo electrónico" >
                            </div>
                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <span wire:ignore><input type="tel" id="telefono"></span>
                                <input type="hidden" id="telefono_completo" wire:model="telefono">
                            </div>
                            <div class="input-group">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" wire:model="contrasenia" placeholder="Ingresa tu contraseña">
                            </div>


                            <!-- <div class="error-message">{{ $error }}</div> -->
                            <div class="error-message">
                                <p style="text-align: center">
                                    <span> {{$message_login ?? ''}} </span>
                                </p>
                            </div>
                            <br>

                        </form>
                        <div style="padding-left:40px; padding-right: 40px">
                            <button type="button" wire:click="Autenticar()" class="btn {{ $isLoading ? 'disabled' : '' }}" {{ $isLoading ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="Autenticar">
                                   <div style="display: flex; justify-content: center">
                                       Iniciar sesión
                                    </div>
                                </span>
                                <span wire:loading wire:target="Autenticar" >

                                    <div style="display: flex; justify-content: center">
                                        <div class="loader" style="margin-right: 10px; "></div> Autenticando...
                                    </div>
                                </span>
                            </button>
                        </div>
                        <!-- fin de seccion de logueo normal -->

                        <div class="login-footer">
                            <a href="#" wire:click="$set('recuclave',true);">¿Crear o Recuperar contraseña?</a>
                        </div>


                    </div>




                @else

                    <!-- seccion de Recuperacion de contrasenia -->
                    <div>
                        <div class="login-header">
                            <h1>Recuperación de Acceso</h1>
                            <p>Ingrese el correo electrónico registrado en su cuenta</p>
                        </div>
                        <form action="">
                            <div class="input-group">
                                <label for="ruc">Correo de recuperación </label>
                                <input type="text" id="ruc" wire:model="emailrescue" placeholder="correo electronico" >
                            </div>
                            <div class="input-group">
                                <span style="color:#0b5ed7;"> {{$message_rescue }} </span>
                            </div>
                        </form>
                        <button type="button" wire:click="RecuperarClave()" class="btn {{ $isLoading ? 'disabled' : '' }} " {{ $isLoading ? 'disabled' : '' }} style="{{$enviado ? 'display:none' : ''}}">
                            <span wire:loading.remove wire:target="RecuperarClave"> Enviar </span>
                            <span wire:loading wire:target="RecuperarClave" class="loader-text">
                            <div class="loader"></div>
                            Solicitando recuperación...
                    </span>
                        </button>

                        <div class="login-footer">
                            <a href="#" wire:click="$set('recuclave',false);">Volver al inicio de sesión</a>
                        </div>


                    </div>
                    <!-- dfin de seccion de recuperacion de contrasenia -->

                @endif




            </div><!-- fin de login box -->



            <!-- Estilos para el loadin -->
            <style>
                /* HTML: <div class="loader"></div> */
                .loader {
                    width: 30px;
                    --b: 8px;
                    aspect-ratio: 1;
                    border-radius: 50%;
                    padding: 1px;
                    background: conic-gradient(#0000 10%, #ffffff) content-box;
                    -webkit-mask:
                        repeating-conic-gradient(#0000 0deg,#000 1deg 20deg,#0000 21deg 36deg), radial-gradient(farthest-side,#0000 calc(100% - var(--b) - 1px),#000 calc(100% - var(--b)));
                    -webkit-mask-composite: destination-in;
                    mask-composite: intersect;
                    animation:l4 1s infinite steps(10);

                }
                @keyframes l4 {to{transform: rotate(1turn)}}
            </style>

        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
        <script>
            document.addEventListener('livewire:initialized', function () {
                // Inicializar el plugin
                const input = document.querySelector("#telefono");
                const iti = window.intlTelInput(input, {
                    initialCountry: "pe", // Peru por defecto
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
                });

                // Actualizar el valor del telefono completo cuando cambie
                input.addEventListener('input', function() {
                    const numeroCompleto = iti.getNumber(); // Formato E.164 (+593xxxxxxxxx)
                    document.getElementById('telefono_completo').value = numeroCompleto;
                    // Disparar evento para que Livewire detecte el cambio
                    document.getElementById('telefono_completo').dispatchEvent(new Event('input'));
                });

                // También actualizar cuando cambie el país
                input.addEventListener('countrychange', function() {
                    const numeroCompleto = iti.getNumber();
                    document.getElementById('telefono_completo').value = numeroCompleto;
                    document.getElementById('telefono_completo').dispatchEvent(new Event('input'));
                });

                // Para mantener el valor al recargar la página
                Livewire.on('telefono-actualizado', phoneNumber => {
                    if (phoneNumber) {
                        iti.setNumber(phoneNumber);
                    }
                });
            });


        </script>

    </div>
    @endif

</div>

<?php

use Livewire\Volt\Component;

new class extends Component {

    public $datos_usuario = null;
    public $renovarDatos = false;
    public $renovarDatosEmail = false;
    public $renovarDatosTelefono = false;
    public $renovarDatosPassword = false;
    public $activeTab = 'datos'; // Tab activo


    public $user_data = [];

    // Campos para edición
    public $razon_social = '';
    public $ruc = '';
    public $email = '';
    public $telefono = '';
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    public $id_user = 0;

    public function mount(){

        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();

        if(isset($this->user_data) )
        {
            $this->razon_social = Session::get('user_data.razon_social');
            $this->ruc = Session::get('user_data.ruc');
            $this->email = Session::get('user_data.email');
            $this->telefono = Session::get('user_data.telefono');
            $this->current_password = '';
            $this->new_password = '';
            $this->id_user = Session::get('user_data.id');
        }

        //dd($this->razon_social);


    }

    public function setActiveTab($tab){
        $this->activeTab = $tab;
    }

    public function VerRenovarDatos($dato){

        if($dato==='r_email'){
            $this->renovarDatosEmail = true;
        }
        elseIf($dato==='r_telefono'){
            $this->renovarDatosTelefono = true;
        }
        elseif($dato==='r_password'){
            $this->renovarDatosPassword = true;


            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';

        }


    }

    public function cancelarEdicion(){

        //$this->renovarDatos = false;
        $this->renovarDatosEmail = false;
        $this->renovarDatosTelefono = false;
        $this->renovarDatosPassword = false;

        // Restaurar valores originales
        $this->razon_social = Session::get('user_data.razon_social');
        $this->ruc = Session::get('user_data.ruc');
        $this->email = Session::get('user_data.email');
        $this->telefono = Session::get('user_data.telefono');
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation='';

        $this->resetValidation();




    }

    public function actualizarDatosEmail(){
        // Validar datos
        //sleep(5);
        $this->validate([
            'email' => 'required|email|max:255',
        ]);

        /*
        $datos_pa_act = [
            'id' => $this->id_user,
            'password' => $this->new_password,
            'email' => $this->email,
            'telefono' => $this->telefono,
        ]; */




        $this->ActualizarDatosApi();

        if(!isset($this->datae)){

            Session::put('user_data.email',$this->email);

            $this->renovarDatosEmail = false;

            session()->flash('success', 'Email actualizado.');
            //dd($datos_pa_act);
        }






    }


    public function actualizarDatosTelefono(){
        $this->validate([
            'telefono' => 'required|string|max:20'
        ]);



        $this->ActualizarDatosApi();

        if(!isset($this->datae)){

            Session::put('user_data.telefono',$this->telefono);

            $this->renovarDatosTelefono = false;

            session()->flash('success', 'Telefono actualizado.');
        }





    }

    public function actualizarDatosPassword(){

        $cont = 0;

        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ],
        [   'current_password.required'=>'Se requiere contraseña actual.',
            'new_password.required'=> 'Se requiere nueva contraseña.',
            'new_password.confirmed'=>'Confirmación de nueva contraseña Faltante o es incorrecta']);


        if($this->current_password == Session::get('user_data.password'))
        {
            $cont = $cont + 1;
        }
        else{
            session()->flash('error', 'Tu contraseña actual es incorrecta.');
        }

        if($this->new_password == $this->new_password_confirmation)
        {
            $cont = $cont + 1;
        }

        if($cont == 2){

           /* $datos_pa_act = [
                'id' => $this->id_user,
                'password' => $this->new_password,
                'email' => $this->email,
                'telefono' => $this->telefono,
            ]; */

            //dd($datos_pa_act);

            $this->ActualizarDatosApi();

            if(!isset($this->datae)){

                Session::put('user_data.password',$this->new_password);

                $this->renovarDatosPassword = false;
                $this->current_password = '';
                $this->new_password = '';
                $this->new_password_confirmation = '';

                session()->flash('success', 'Contraseña actualizada.');
                //dd($datos_pa_act);
            }

        }



    }




    public $datae=null;
    public $mensaje_servicio = '';

    public function ActualizarDatosApi()
    {

        $this->datae = null;
       // $this->isLoading = true;

        try {
            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])->post(config('services.sistema25.base_url').'/customer/edit', [
                'id' => $this->id_user,     // Cambiar ruc por email
                'password' => filled($this->new_password) ? $this->new_password : Session::get('user_data.password'),
                'email' => $this->email,
                'telefono' => $this->telefono,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Verificar si la peticion fue exitosa
                if ($data['status'] === 'success') {



                }

                if($data['status'] === 'error'){
                    $this->mensaje_servicio = $data['message'];
                }
            }
            else{

                // Si hubo error al actualizar los datos
                $this->datae = $response->json();
                $this->mensaje_servicio = $this->datae['message'];
                return null;
            }



        } catch (\Exception $e) {
            logger('Error en la actualización de datos: ' . $e->getMessage());
            session()->flash('error',$e->getMessage());
            $this->error = 'Error de conexión';
            return null;
        }
        finally {
            //$this->isLoading = false;
        }

    }





}; ?>

<div>
    @include('livewire.clientes.partials.perfil-redesign')
    @if(false)

    <style>

        .loader_a {
            width: 12px;
            height: 12px;
            border: 2px solid #FFF;
            border-bottom-color: #FF3D00;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

    </style>
    <style>
        .tab-active {
            background-color: #f63b3b;
            color: white;
        }

        .tab-inactive {
            background-color: #F3F4F6;
            color: #6B7280;
        }

        .tab-inactive:hover {
            background-color: #E5E7EB;
            color: #374151;
        }

        .form-input {
            @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
        }

        .form-input:disabled {
            @apply bg-gray-100 cursor-not-allowed;
        }

        .btn-primary {
            @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
        }

        .btn-secondary {
            @apply bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
        }

        .btn-danger {
            @apply bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
        }
    </style>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="mb-4 p-1 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{session('error')}}
                {{ $mensaje_servicio }}
            </div>
        </div>
    @endif



    <!-- Tarjeta Principal -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <!-- Título -->
            {{-- <h1 class="text-2xl font-bold text-gray-800 mb-6">Perfil de Usuario</h1>--}}


            {{-- Pestañas de Navegación --}}
            <div class="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg">
                <button
                    wire:click="setActiveTab('datos')"
                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 {{ $activeTab === 'datos' ? 'tab-active' : 'tab-inactive' }}"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Datos Personales
                </button>

                <button
                    wire:click="setActiveTab('telefono')"
                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 {{ $activeTab === 'telefono' ? 'tab-active' : 'tab-inactive' }}"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Teléfono
                </button>

                <button
                    wire:click="setActiveTab('password')"
                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 {{ $activeTab === 'password' ? 'tab-active' : 'tab-inactive' }}"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Contraseña
                </button>
            </div>

            {{--  Contenido de las Pestañas --}}
            <div class="min-h-96">

                {{--  Tab: Datos Personales --}}
                @if($activeTab === 'datos')
                    <div class="space-y-6">


                                            <!-- Botón de Modificar Datos Email -->
                                            <div class="flex justify-start mb-6">
                                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Información Personal </h2>

                                                <div style="margin-left: 20px" class="text-sm mt-1">
                                                @if($renovarDatosEmail)
                                                    <div class="space-x-2">
                                                        <a wire:click="cancelarEdicion"
                                                                class="bg-gray-200 hover:bg-gray-300  text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200" >
                                                            Cancelar
                                                        </a>
                                                        <button wire:click="actualizarDatosEmail()"  {{ $email!==Session::get('user_data.email') ? '' : 'disabled' }}
                                                                class="hover:bg-blue-500 bg-blue-400 text-white font-medium py-1 px-4 rounded-md transition duration-200
                                                                {{ $email === Session::get('user_data.email') ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                            Guardar Cambios
                                                            <div wire:loading wire:target="actualizarDatosEmail" class="ml-1">
                                                                <span class="loader_a"></span>
                                                            </div>
                                                        </button>


                                                    </div>
                                                @else
                                                    <a wire:click="VerRenovarDatos('r_email')"
                                                            class="bg-gray-300 hover:bg-blue-500 hover:text-white text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200">
                                                        Modificar Email
                                                    </a>
                                                @endif
                                                </div>
                                            </div>



                        <!-- Razón Social -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Razón Social</label>
                            <input
                                type="text"
                                wire:model="razon_social"
                                class="form-input bg-gray-100 w-80 rounded-lg p-2" readonly >
                            @error('razon_social') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- RUC -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">RUC</label>
                            <input
                                type="text"
                                wire:model="ruc"
                                class="form-input font-mono bg-gray-100 w-80 rounded-lg p-2" readonly>
                            @error('ruc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                            <input
                                type="email"
                                wire:model.live="email"
                                class="form-input {{ !$renovarDatosEmail ? 'bg-gray-100' : 'bg-white border border-blue-500 border-2' }} w-80 rounded-lg p-2"
                                {{ !$renovarDatosEmail ? 'readonly' : '' }}
                            >
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>


                    </div>
                @endif

               {{--  <!-- Tab: Teléfono --> --}}
                @if($activeTab === 'telefono')
                    <div class="space-y-6">



                                    <!-- Botón de Modificar Telefono -->
                                    <div class="flex justify-start mb-6">
                                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Número de Teléfono </h2>

                                        <div style="margin-left: 20px" class="text-sm mt-1">
                                            @if($renovarDatosTelefono)
                                                <div class="space-x-2">
                                                    <a wire:click="cancelarEdicion"
                                                       class="bg-gray-200 hover:bg-gray-300  text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200" >
                                                        Cancelar
                                                    </a>
                                                    <button wire:click="actualizarDatosTelefono()"  {{ $telefono!==Session::get('user_data.telefono') ? '' : 'disabled' }}
                                                       class="hover:bg-blue-500 bg-blue-400 text-white font-medium py-1 px-4 rounded-md transition duration-200
                                                        {{ $telefono === Session::get('user_data.telefono') ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                        Guardar Cambios
                                                        <div wire:loading wire:target="actualizarDatosTelefono" class="ml-1">
                                                            <span class="loader_a"></span>
                                                        </div>
                                                    </button>
                                                </div>
                                            @else
                                                <a wire:click="VerRenovarDatos('r_telefono')"
                                                        class="bg-gray-300 hover:bg-blue-500 hover:text-white text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200">
                                                    Modificar Teléfono
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                        <div class="bg-blue-50 p-4 rounded-lg mb-4">
                            <p class="text-sm text-blue-800">
                                Tu teléfono actual: <strong>{{ Session::get('user_data.telefono') ?? 'No registrado' }}</strong>
                            </p>
                        </div>

                        <form wire:submit.prevent="actualizarTelefono">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Teléfono</label>
                                    <input
                                        type="tel"
                                        wire:model.live="telefono"
                                        class="form-input {{ !$renovarDatosTelefono ? 'bg-gray-100' : 'bg-white border border-blue-500 border-2' }} w-80 rounded-lg p-2"
                                        {{ !$renovarDatosTelefono ? 'readonly' : '' }}   placeholder="+51 999 123 456"
                                    >
                                    @error('telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>


                            </div>
                        </form>
                    </div>
                @endif

                {{-- <!-- Tab: Contrasena -->--}}
                @if($activeTab === 'password')
                    <div class="space-y-6">

                                                <!-- Botón de Modificar Datos Contrasenia -->
                                                <div class="flex justify-start mb-6">
                                                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Cambiar Contraseña </h2>

                                                    <div style="margin-left: 20px" class="text-sm mt-1">
                                                        @if($renovarDatosPassword)
                                                            <div class="space-x-2">
                                                                <a wire:click="cancelarEdicion"
                                                                   class="bg-gray-200 hover:bg-gray-300  text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200" >
                                                                    Cancelar
                                                                </a>
                                                                <a wire:click="actualizarDatosPassword()"
                                                                   class="hover:bg-blue-500 bg-blue-400 text-white font-medium py-1 px-4 rounded-md transition duration-200">
                                                                    Guardar Cambios
                                                                    <div wire:loading wire:target="actualizarDatosPassword" class="ml-1">
                                                                        <span class="loader_a"></span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <a wire:click="VerRenovarDatos('r_password')"
                                                               class="bg-gray-300 hover:bg-blue-500 hover:text-white text-zinc-700 font-medium py-1 px-4 rounded-md transition duration-200">
                                                                Modificar Contraseña
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>


                        <div class="bg-yellow-200 p-4 rounded-lg mb-4">
                            <p class="text-sm text-gray-800">
                                Asegúrate de usar una contraseña segura con al menos 8 caracteres.
                            </p>
                        </div>


                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                                    <input
                                        type="password"
                                        wire:model="current_password"
                                        class="form-input {{ !$renovarDatosPassword ? 'bg-gray-100' : 'bg-white border border-blue-500 border-2' }} w-80 rounded-lg p-2"
                                        {{ !$renovarDatosPassword ? 'readonly' : '' }}
                                        placeholder="••••••••••••"
                                    >
                                    @error('current_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                    <input
                                        type="password"
                                        wire:model="new_password"
                                        class="form-input {{ !$renovarDatosPassword ? 'bg-gray-100' : 'bg-white border border-blue-500 border-2' }} w-80 rounded-lg p-2"
                                        {{ !$renovarDatosPassword ? 'readonly' : '' }}
                                        placeholder="••••••••••••"
                                    >
                                    @error('new_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                    <input
                                        type="password"
                                        wire:model="new_password_confirmation"
                                        class="form-input {{ !$renovarDatosPassword ? 'bg-gray-100' : 'bg-white border border-blue-500 border-2' }} w-80 rounded-lg p-2"
                                        {{ !$renovarDatosPassword ? 'readonly' : '' }}
                                        placeholder="••••••••••••"
                                    >
                                    @error('new_password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="btn-danger w-full">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Cambiar Contraseña
                                </button>
                            </div>

                    </div>
                @endif

            </div>
        </div>
    </div>
    @endif
</div>

<main class="auth-layout" id="main-content">
    <section class="auth-brand" aria-label="Almacenes SOS">
        <img class="auth-logo" src="{{ asset('imgcc/logos_empresas_edit.png') }}" alt="Almacenes y Depósitos de Aduanas SOS">
        <div class="auth-pitch">
            <p class="auth-eyebrow">Portal de clientes</p>
            <h1>Tu operación logística, clara y siempre disponible.</h1>
            <p>Consulta comprobantes y órdenes desde cualquier dispositivo, con información ordenada y fácil de entender.</p>
            <div class="auth-features" aria-label="Ventajas">
                <span class="auth-feature"><x-sos-icon name="check" class="icon-sm" /> Estado de operaciones</span>
                <span class="auth-feature"><x-sos-icon name="check" class="icon-sm" /> Documentos centralizados</span>
                <span class="auth-feature"><x-sos-icon name="check" class="icon-sm" /> Acceso seguro</span>
            </div>
        </div>
        <p class="auth-footer">© {{ date('Y') }} Almacenes y Depósitos de Aduanas SOS</p>
    </section>

    <section class="auth-main">
        <div class="auth-card">
            @if(!$recuclave)
                <header class="auth-card-header">
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus datos para acceder al portal.</p>
                </header>

                <form class="auth-form" wire:submit="Autenticar">
                    @if($message_login || $error)
                        <div class="auth-status error" role="alert">
                            <x-sos-icon name="alert" class="icon-sm" />
                            <span>No pudimos iniciar sesión. Revisa el correo, teléfono y contraseña.</span>
                        </div>
                    @endif

                    <div class="field">
                        <label for="login-email">Correo electrónico</label>
                        <input
                            class="control"
                            id="login-email"
                            type="email"
                            wire:model="email"
                            autocomplete="username"
                            placeholder="nombre@empresa.com"
                            required
                        >
                        @error('email') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label for="login-phone">Teléfono</label>
                        <div class="input-with-prefix">
                            <span class="input-prefix" aria-hidden="true"><span class="flag-pe"></span> +51</span>
                            <input
                                class="control"
                                id="login-phone"
                                type="tel"
                                inputmode="numeric"
                                wire:model="telefono"
                                autocomplete="tel-national"
                                maxlength="9"
                                placeholder="999 999 999"
                                required
                            >
                        </div>
                        @error('telefono') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field" x-data="{ visible: false }">
                        <label for="login-password">Contraseña</label>
                        <div class="password-control">
                            <input
                                class="control"
                                id="login-password"
                                :type="visible ? 'text' : 'password'"
                                wire:model="contrasenia"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                required
                            >
                            <button class="password-toggle" type="button" @click="visible = !visible" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <span x-text="visible ? 'Ocultar' : 'Mostrar'"></span>
                            </button>
                        </div>
                        @error('contrasenia') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="Autenticar">
                        <span wire:loading.remove wire:target="Autenticar">Iniciar sesión</span>
                        <span wire:loading wire:target="Autenticar">Verificando…</span>
                    </button>
                </form>

                <div class="auth-actions">
                    <button class="text-link" type="button" wire:click="$set('recuclave', true)">¿Olvidaste tu contraseña?</button>
                </div>
            @else
                <header class="auth-card-header">
                    <h2>Recupera tu acceso</h2>
                    <p>Te enviaremos instrucciones si el correo está registrado.</p>
                </header>

                @if($enviado)
                    <div class="auth-status success" role="status">
                        <x-sos-icon name="check" class="icon-sm" />
                        <span>Solicitud recibida. Revisa tu bandeja de entrada y correo no deseado.</span>
                    </div>
                    <div class="auth-actions">
                        <button class="btn btn-primary" type="button" wire:click="$set('recuclave', false)">Volver al inicio de sesión</button>
                    </div>
                @else
                    <form class="auth-form" wire:submit="RecuperarClave">
                        @if($error)
                            <div class="auth-status error" role="alert">
                                <x-sos-icon name="alert" class="icon-sm" /><span>No pudimos procesar la solicitud. Inténtalo nuevamente.</span>
                            </div>
                        @endif
                        <div class="field">
                            <label for="recovery-email">Correo electrónico</label>
                            <input
                                class="control"
                                id="recovery-email"
                                type="email"
                                wire:model="emailrescue"
                                autocomplete="email"
                                placeholder="nombre@empresa.com"
                                required
                            >
                            @error('emailrescue') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="RecuperarClave">
                            <span wire:loading.remove wire:target="RecuperarClave">Enviar instrucciones</span>
                            <span wire:loading wire:target="RecuperarClave">Enviando…</span>
                        </button>
                    </form>
                    <div class="auth-actions">
                        <button class="text-link" type="button" wire:click="$set('recuclave', false)">Volver al inicio de sesión</button>
                    </div>
                @endif
            @endif
        </div>
    </section>
</main>

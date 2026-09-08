<div class="profile-shell">
    @if(session('success'))
        <div class="auth-status success profile-notice" role="status">
            <x-sos-icon name="check" class="icon-sm" /><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="auth-status error profile-notice" role="alert">
            <x-sos-icon name="alert" class="icon-sm" /><span>No pudimos guardar el cambio. Revisa los datos e inténtalo nuevamente.</span>
        </div>
    @endif

    <div class="tabs" role="tablist" aria-label="Secciones del perfil">
        <button class="tab" type="button" role="tab" aria-selected="{{ $activeTab === 'datos' ? 'true' : 'false' }}" wire:click="setActiveTab('datos')">
            <x-sos-icon name="user" class="icon-sm" /> Datos
        </button>
        <button class="tab" type="button" role="tab" aria-selected="{{ $activeTab === 'telefono' ? 'true' : 'false' }}" wire:click="setActiveTab('telefono')">
            <x-sos-icon name="phone" class="icon-sm" /> Teléfono
        </button>
        <button class="tab" type="button" role="tab" aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}" wire:click="setActiveTab('password')">
            <x-sos-icon name="lock" class="icon-sm" /> Seguridad
        </button>
    </div>

    @if($activeTab === 'datos')
        <section class="card profile-card">
            <header class="card-header">
                <div><h2>Información de la empresa</h2><p>Datos asociados a la cuenta del portal.</p></div>
            </header>
            <div class="card-body">
                @if($renovarDatosEmail)
                    <form class="profile-form" wire:submit="actualizarDatosEmail">
                        <div class="field">
                            <label for="profile-email">Nuevo correo electrónico</label>
                            <input class="control" id="profile-email" type="email" wire:model.live="email" autocomplete="email" required>
                            @error('email') <span class="field-error">{{ $message }}</span> @enderror
                            <span class="field-hint">Usaremos este correo para notificaciones y recuperación de acceso.</span>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="actualizarDatosEmail">
                                <span wire:loading.remove wire:target="actualizarDatosEmail">Guardar correo</span>
                                <span wire:loading wire:target="actualizarDatosEmail">Guardando…</span>
                            </button>
                            <button class="btn btn-secondary" type="button" wire:click="cancelarEdicion">Cancelar</button>
                        </div>
                    </form>
                @else
                    <dl class="profile-list">
                        <div class="profile-row"><dt>Razón social</dt><dd>{{ $razon_social ?: 'No registrado' }}</dd></div>
                        <div class="profile-row"><dt>RUC</dt><dd>{{ $ruc ?: 'No registrado' }}</dd></div>
                        <div class="profile-row">
                            <dt>Correo electrónico</dt><dd>{{ $email ?: 'No registrado' }}</dd>
                            <button class="btn btn-secondary btn-sm" type="button" wire:click="VerRenovarDatos('r_email')">Modificar</button>
                        </div>
                    </dl>
                @endif
            </div>
        </section>
    @endif

    @if($activeTab === 'telefono')
        <section class="card profile-card">
            <header class="card-header">
                <div><h2>Teléfono de contacto</h2><p>Se utiliza para el acceso y comunicaciones importantes.</p></div>
            </header>
            <div class="card-body">
                @if($renovarDatosTelefono)
                    <form class="profile-form" wire:submit="actualizarDatosTelefono">
                        <div class="field">
                            <label for="profile-phone">Nuevo teléfono</label>
                            <div class="input-with-prefix">
                                <span class="input-prefix" aria-hidden="true"><span class="flag-pe"></span> +51</span>
                                <input class="control" id="profile-phone" type="tel" inputmode="tel" wire:model.live="telefono" autocomplete="tel-national" required>
                            </div>
                            @error('telefono') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="actualizarDatosTelefono">
                                <span wire:loading.remove wire:target="actualizarDatosTelefono">Guardar teléfono</span>
                                <span wire:loading wire:target="actualizarDatosTelefono">Guardando…</span>
                            </button>
                            <button class="btn btn-secondary" type="button" wire:click="cancelarEdicion">Cancelar</button>
                        </div>
                    </form>
                @else
                    <dl class="profile-list">
                        <div class="profile-row">
                            <dt>Teléfono actual</dt><dd>{{ $telefono ?: 'No registrado' }}</dd>
                            <button class="btn btn-secondary btn-sm" type="button" wire:click="VerRenovarDatos('r_telefono')">Cambiar teléfono</button>
                        </div>
                    </dl>
                @endif
            </div>
        </section>
    @endif

    @if($activeTab === 'password')
        <section class="card profile-card">
            <header class="card-header">
                <div><h2>Actualiza tu contraseña</h2><p>Los campos siempre aparecen vacíos y la contraseña nunca se muestra.</p></div>
            </header>
            <div class="card-body">
                <div class="info-panel"><x-sos-icon name="lock" class="icon-sm" /> Usa al menos 8 caracteres, una mayúscula, un número y un símbolo.</div>
                <form
                    class="profile-form"
                    wire:submit="actualizarDatosPassword"
                    x-data="{ password: @entangle('new_password').live }"
                >
                    <div class="field">
                        <label for="current-password">Contraseña actual</label>
                        <input class="control" id="current-password" type="password" wire:model="current_password" autocomplete="current-password" required>
                        @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="new-password">Nueva contraseña</label>
                        <input class="control" id="new-password" type="password" wire:model.live="new_password" x-model="password" autocomplete="new-password" minlength="8" required>
                        <div class="password-meter" aria-hidden="true">
                            <span :style="'width:' + ([password.length >= 8, /[A-Z]/.test(password), /[0-9]/.test(password), /[^A-Za-z0-9]/.test(password)].filter(Boolean).length * 25) + '%'"
                                  :class="{ 'strong': password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password) }"></span>
                        </div>
                        @error('new_password') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="confirm-password">Confirma la nueva contraseña</label>
                        <input class="control" id="confirm-password" type="password" wire:model="new_password_confirmation" autocomplete="new-password" required>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="actualizarDatosPassword">
                            <span wire:loading.remove wire:target="actualizarDatosPassword">Actualizar contraseña</span>
                            <span wire:loading wire:target="actualizarDatosPassword">Actualizando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endif
</div>

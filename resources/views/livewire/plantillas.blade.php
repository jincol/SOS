<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    //


    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Herramientas HTML5 con Tailwind CSS</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 min-h-screen">
    <!-- Layout Responsivo de Dos Columnas -->
    <section class="mb-12 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Layout Responsivo de Dos Columnas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna 1 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Columna 1</h3>
                <p class="text-gray-600">Este contenido se apila verticalmente en pantallas pequeñas y se muestra en columna izquierda en pantallas medianas y grandes.</p>
                <div class="mt-4 h-32 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-blue-600 font-medium">Contenido de ejemplo</span>
                </div>
            </div>

            <!-- Columna 2 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Columna 2</h3>
                <p class="text-gray-600">Este contenido se apila debajo de la columna 1 en pantallas pequeñas y se muestra en columna derecha en pantallas medianas y grandes.</p>
                <div class="mt-4 h-32 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600 font-medium">Contenido de ejemplo</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabla Responsiva -->
    <section class="mb-12 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Tabla de Registros</h2>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">001</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Juan Pérez</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">juan@email.com</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="text-blue-600 hover:text-blue-900">Ver</button>
                            <button class="text-yellow-600 hover:text-yellow-900">Editar</button>
                            <button class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">002</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">María García</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">maria@email.com</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-10</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="text-blue-600 hover:text-blue-900">Ver</button>
                            <button class="text-yellow-600 hover:text-yellow-900">Editar</button>
                            <button class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">003</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Carlos López</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">carlos@email.com</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-12</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="text-blue-600 hover:text-blue-900">Ver</button>
                            <button class="text-yellow-600 hover:text-yellow-900">Editar</button>
                            <button class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="mb-12 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Cards de Ejemplo</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600"></div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Card con Imagen</h3>
                    <p class="text-gray-600 mb-4">Esta es una descripción de ejemplo para mostrar cómo se ve el contenido en una card.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Hace 2 días</span>
                        <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Ver más</button>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">$</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-semibold text-gray-800">Card con Icono</h3>
                        <p class="text-gray-500">Estadísticas</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">Información importante sobre métricas y datos del sistema.</p>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Tag 1</button>
                    <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Tag 2</button>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Card Simple</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total de usuarios:</span>
                        <span class="font-bold text-gray-800">1,234</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Activos hoy:</span>
                        <span class="font-bold text-green-600">567</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Registros nuevos:</span>
                        <span class="font-bold text-blue-600">89</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Listado de Herramientas para APIs -->
    <section class="mb-12 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Herramientas para Sistemas con APIs</h2>
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">Componentes Recomendados</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Estados de Carga -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-700">Estados de Carga</h4>
                        <div class="space-y-2">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent mr-3"></div>
                                <span class="text-sm text-gray-600">Spinner de carga</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="animate-pulse flex space-x-2">
                                    <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                                    <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                                </div>
                                <span class="text-sm text-gray-600 mt-2 block">Skeleton loader</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes de Estado -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-700">Mensajes de Estado</h4>
                        <div class="space-y-2">
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <span class="text-sm text-green-800">✓ Datos cargados exitosamente</span>
                            </div>
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <span class="text-sm text-red-800">✗ Error al cargar datos</span>
                            </div>
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <span class="text-sm text-yellow-800">⚠ Sin datos disponibles</span>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-700">Paginación</h4>
                        <div class="flex items-center space-x-2">
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Anterior</button>
                            <button class="px-3 py-1 bg-blue-500 text-white rounded">1</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">2</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">3</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Siguiente</button>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-700">Filtros y Búsqueda</h4>
                        <div class="space-y-2">
                            <input type="text" placeholder="Buscar..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Todos los estados</option>
                                <option>Activo</option>
                                <option>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Lista de Herramientas Adicionales -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="font-semibold text-gray-700 mb-4">Herramientas y Componentes Adicionales</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Modales</h5>
                            <p class="text-sm text-gray-600">Para mostrar detalles o formularios</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Tooltips</h5>
                            <p class="text-sm text-gray-600">Información adicional al hover</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Breadcrumbs</h5>
                            <p class="text-sm text-gray-600">Navegación de ubicación</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Badges</h5>
                            <p class="text-sm text-gray-600">Etiquetas de estado</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Progress Bars</h5>
                            <p class="text-sm text-gray-600">Barras de progreso</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Dropdowns</h5>
                            <p class="text-sm text-gray-600">Menús desplegables</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Tabs</h5>
                            <p class="text-sm text-gray-600">Pestañas de navegación</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Alerts</h5>
                            <p class="text-sm text-gray-600">Notificaciones y alertas</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h5 class="font-medium text-gray-700">Forms</h5>
                            <p class="text-sm text-gray-600">Formularios de entrada</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Formularios con Inputs y Labels -->
    <section class="mb-12 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Formularios con Inputs y Labels</h2>

        <!-- Formulario Básico -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Formulario Básico</h3>
            <form class="space-y-4">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ingresa tu nombre completo">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="ejemplo@correo.com">
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="+51 999 999 999">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="••••••••">
                </div>

                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                    <input type="date" id="fecha" name="fecha"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                    <input type="number" id="numero" name="numero" min="1" max="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="25">
                </div>
            </form>
        </div>

        <!-- Formulario con Selects y Textarea -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Selects y Textarea</h3>
            <form class="space-y-4">
                <div>
                    <label for="pais" class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <select id="pais" name="pais"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecciona un país</option>
                        <option value="pe">Perú</option>
                        <option value="ar">Argentina</option>
                        <option value="cl">Chile</option>
                        <option value="co">Colombia</option>
                        <option value="mx">México</option>
                    </select>
                </div>

                <div>
                    <label for="genero" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                    <select id="genero" name="genero"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecciona una opción</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                        <option value="no-especificar">Prefiero no especificar</option>
                    </select>
                </div>

                <div>
                    <label for="comentarios" class="block text-sm font-medium text-gray-700 mb-1">Comentarios</label>
                    <textarea id="comentarios" name="comentarios" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                              placeholder="Escribe tus comentarios aquí..."></textarea>
                </div>
            </form>
        </div>

        <!-- Formulario con Checkboxes y Radios -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Checkboxes y Radio Buttons</h3>
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Intereses (selecciona varios)</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="tech" name="intereses" value="tecnologia"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="tech" class="ml-2 text-sm text-gray-700">Tecnología</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="sports" name="intereses" value="deportes"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="sports" class="ml-2 text-sm text-gray-700">Deportes</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="music" name="intereses" value="musica"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="music" class="ml-2 text-sm text-gray-700">Música</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="travel" name="intereses" value="viajes"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="travel" class="ml-2 text-sm text-gray-700">Viajes</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de contacto preferido</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="email_contact" name="contacto" value="email"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="email_contact" class="ml-2 text-sm text-gray-700">Email</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="phone_contact" name="contacto" value="telefono"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="phone_contact" class="ml-2 text-sm text-gray-700">Teléfono</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="whatsapp_contact" name="contacto" value="whatsapp"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="whatsapp_contact" class="ml-2 text-sm text-gray-700">WhatsApp</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="terminos" name="terminos"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="terminos" class="ml-2 text-sm text-gray-700">
                        Acepto los <a href="#" class="text-blue-600 hover:text-blue-800">términos y condiciones</a>
                    </label>
                </div>
            </form>
        </div>

        <!-- Formulario con Estados de Validación -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Estados de Validación</h3>
            <form class="space-y-4">
                <div>
                    <label for="input_success" class="block text-sm font-medium text-gray-700 mb-1">Input válido</label>
                    <input type="text" id="input_success" name="input_success"
                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Entrada válida" value="ejemplo@correo.com">
                    <p class="mt-1 text-sm text-green-600">✓ Email válido</p>
                </div>

                <div>
                    <label for="input_error" class="block text-sm font-medium text-gray-700 mb-1">Input con error</label>
                    <input type="text" id="input_error" name="input_error"
                           class="w-full px-3 py-2 border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Entrada con error" value="email-invalido">
                    <p class="mt-1 text-sm text-red-600">✗ El formato del email no es válido</p>
                </div>

                <div>
                    <label for="input_warning" class="block text-sm font-medium text-gray-700 mb-1">Input con advertencia</label>
                    <input type="password" id="input_warning" name="input_warning"
                           class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                           placeholder="Contraseña" value="123">
                    <p class="mt-1 text-sm text-yellow-600">⚠ La contraseña es muy corta</p>
                </div>

                <div>
                    <label for="input_disabled" class="block text-sm font-medium text-gray-500 mb-1">Input deshabilitado</label>
                    <input type="text" id="input_disabled" name="input_disabled" disabled
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                           placeholder="Campo deshabilitado" value="No editable">
                </div>
            </form>
        </div>

        <!-- Formulario en Grid -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Formulario en Grid</h3>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre_grid" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" id="nombre_grid" name="nombre_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nombre">
                </div>

                <div>
                    <label for="apellido_grid" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" id="apellido_grid" name="apellido_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Apellido">
                </div>

                <div>
                    <label for="email_grid" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email_grid" name="email_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="ejemplo@correo.com">
                </div>

                <div>
                    <label for="telefono_grid" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" id="telefono_grid" name="telefono_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="+51 999 999 999">
                </div>

                <div class="md:col-span-2">
                    <label for="direccion_grid" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" id="direccion_grid" name="direccion_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Dirección completa">
                </div>

                <div>
                    <label for="ciudad_grid" class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" id="ciudad_grid" name="ciudad_grid"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ciudad">
                </div>

                <div>
                    <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                    <input type="text" id="codigo_postal" name="codigo_postal"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="00000">
                </div>

                <div class="md:col-span-2 flex justify-end space-x-3 mt-6">
                    <button type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </section>
    </body>
    </html>


</div>

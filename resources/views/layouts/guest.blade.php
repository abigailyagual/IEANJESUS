
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Iniciar sesión') - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-10 lg:flex-row lg:items-center lg:gap-12">

        <!-- SECCIÓN IZQUIERDA: PRESENTACIÓN INSTITUCIONAL -->
        <div class="max-w-xl lg:flex-1">

            <!-- IDENTIDAD INSTITUCIONAL -->
            <div class="inline-flex items-center gap-3 rounded-md bg-white px-4 py-3 shadow-sm">

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-sm font-semibold text-white">
                    IE
                </div>

                <div>
                    <div class="text-sm font-semibold">
                        IEANJESÚS
                    </div>

                    <div class="text-xs text-slate-500">
                        La Libertad - Gestión de Informes Mensuales
                    </div>
                </div>

            </div>

            <!-- TÍTULO PRINCIPAL -->
            <h1 class="mt-8 max-w-xl text-4xl font-semibold tracking-tight text-slate-900">
                Gestión y Control de Informes Mensuales
            </h1>

            <!-- DESCRIPCIÓN -->
            <p class="mt-4 max-w-lg text-base text-slate-600">
                Plataforma web institucional para registrar, presentar,
                revisar y aprobar los informes mensuales de los
                departamentos de IEANJESÚS La Libertad.
            </p>

            <!-- TARJETAS INFORMATIVAS -->
            <div class="mt-8 grid gap-3 sm:grid-cols-2">

                <!-- TARJETA 1 -->
                <div class="panel p-4">

                    <div class="text-sm font-medium text-slate-900">
                        Informes mensuales
                    </div>

                    <div class="mt-2 text-sm text-slate-500">
                        Registro, consulta y seguimiento de los informes
                        presentados por cada departamento.
                    </div>

                </div>

                <!-- TARJETA 2 -->
                <div class="panel p-4">

                    <div class="text-sm font-medium text-slate-900">
                        Revisión y aprobación
                    </div>

                    <div class="mt-2 text-sm text-slate-500">
                        Seguimiento de observaciones, correcciones,
                        aprobaciones e historial de los informes.
                    </div>

                </div>

            </div>

        </div>

        <!-- SECCIÓN DERECHA: FORMULARIO DE ACCESO -->
        <div class="mt-10 w-full max-w-md lg:mt-0">

            <div class="panel overflow-hidden">

                <!-- ENCABEZADO DEL FORMULARIO -->
                <div class="border-b border-slate-200 px-6 py-5">

                    <div class="text-lg font-semibold text-slate-900">
                        Acceso al sistema
                    </div>

                    <div class="mt-1 text-sm text-slate-500">
                        Ingrese con su cuenta institucional para gestionar
                        los informes mensuales de su departamento.
                    </div>

                </div>

                <!-- FORMULARIO DE LOGIN -->
                <div class="px-6 py-6">
                    {{ $slot }}
                </div>

            </div>

        </div>

    </div>

</body>
</html>

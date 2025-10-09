<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .background-image-body {
                /* Menggunakan helper asset() untuk path yang terjamin benar */
                background-image: url('{{ asset("assets/img/unnamed.jpg") }}'); 
                
                /* Properti tambahan agar gambar terlihat baik */
                background-size: cover; 
                background-repeat: no-repeat;
                background-attachment: fixed; 
                background-position: center center; 
            }

            /* Tambahan: Opsional untuk membuat kotak login lebih transparan agar gambar terlihat */
            .card-overlay {
                background-color: rgba(42, 37, 102, 0.95); /* Putih dengan sedikit transparansi */
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased background-image-body"> 
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-12 px-12 py-4 shadow-md overflow-hidden sm:rounded-lg card-overlay">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
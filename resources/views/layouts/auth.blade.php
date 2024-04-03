<!DOCTYPE html>
<html lang = "{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset = "utf-8" />
        <meta name = "csrf-token" content = "{{ csrf_token() }}" />
        <meta name = "viewport" content = "width=device-width, initial-scale=1" />
        <title>Kx-safe</title>
        <link rel = "stylesheet" href = "https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" />
        <link rel = "stylesheet" href = "{{ asset('css/app.css') }}" />
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/app.js') }}" defer></script>
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/sweetalert2.js') }}"></script>
    </head>
    <body>
        <div class = "font-sans text-gray-900 antialiased">
            <div class = "min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
                <div>
<<<<<<< HEAD
                    <a href = "">
=======
                    <a href = "/kxsafe">
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
                        <img src = "{{ asset('img/logo.png') }}">
                    </a>
                </div>
                <div class = "w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    @yield("content")
                </div>
            </div>
        </div>
    </body>
</html>
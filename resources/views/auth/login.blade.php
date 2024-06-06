@extends('layouts.auth')
@section('content')
    <form method = "POST" action = "{{ route('login') }}">
        @csrf
        <div>
            <label class = "block font-medium text-sm text-gray-700" for = "email">E-mail</label>
            <input class = "rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id = "email" type = "email" name = "email" required = "required" autofocus = "autofocus" />
        </div>
        <div class = "mt-4">
            <label class = "block font-medium text-sm text-gray-700" for = "password">Senha</label>
            <input class = "rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id = "password" type = "password" name = "password" required = "required" autocomplete = "current-password" />
        </div>
        <div class = "flex items-center justify-end mt-4">
            <button type = "submit" class = "inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 ml-3">Entrar</button>
        </div>
    </form>
    @if ($errors->any())
        <script type = "text/javascript" language = "JavaScript">
            window.onload = function() {
                Swal.fire({
                    icon : "error",
                    title : "Erro",
                    text : "Email ou senha inválidos",
                    confirmButtonColor : "rgb(31, 41, 55)"
                });
            }
        </script>
    @endif
@endsection
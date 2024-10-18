@extends("layouts.app")

@section("content")
    <div class = "container-fluid h-100 px-3">
    <div>
        <h3 class = "header-color mb-3">Dashboard</h3>
    </div>
    <div class = "d-flex justify-content-around">
        <div class = "card-dashboard mx-3 w-100 bg-white rounded shadow-sm custom-scrollbar">
            <div class = "header-card-dashboard d-flex justify-content-between border-bottom">
                <div class = "d-flex flex-column justify-content-center align-items-start ml-3">
                    <span class = "titulo-card-dashboard">Título card</span>
                    <span class = "subtitulo-card-dashboard">Subtítulo</span>
                </div>    
                <div class = "align-self-start m-3">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
            </div>
            <div class = "d-flex flex-column align-items-center m-5">
                <span>Não há nada a mostrar</span>
            </div>
        </div>
        <div class = "card-dashboard mx-3 w-100 bg-white rounded shadow-sm custom-scrollbar">
            <div class = "header-card-dashboard d-flex justify-content-between border-bottom">
                <div class = "d-flex flex-column justify-content-center align-items-start ml-3">
                    <span class = "titulo-card-dashboard">Retiradas em atraso</span>
                    
                </div>    
                <div class = "align-self-start m-3">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
            </div>
            @if(sizeof($pessoas))
                <div class = "d-flex justify-content-center">
                    <table class = "table tabela-funcionarios-dashboard">
                        @foreach ($pessoas as $pessoa)
                            <tr onclick = "produtosEmAtraso({{ $pessoa->id }})">
                                <td width = "5%" class = "td-foto">
                                    <img class = 'foto-funcionario-dashboard' src = '{{ $pessoa->foto }}' onerror = "this.classList.add('d-none');this.nextElementSibling.classList.remove('d-none')" />
                                    <i class = 'fas fa-user d-none'></i>
                                </td>
                                <td width="90%" class = "td-nome">{{ $pessoa->nome }}</td>
                                <td class="text-right" width = "5%">{{ $pessoa->total }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @else
                <div class = "d-flex flex-column align-items-center m-5">
                    <span>Não há nada a mostrar</span>
                </div>
            @endif
        </div>
        <div class = "card-dashboard mx-3 w-100 bg-white rounded shadow-sm custom-scrollbar">
            <div class = "header-card-dashboard d-flex justify-content-between border-bottom">
                <div class = "d-flex flex-column justify-content-center align-items-start ml-3">
                    <span class = "titulo-card-dashboard">Título card</span>
                    <span class = "subtitulo-card-dashboard">Subtítulo</span>
                </div>    
                <div class = "align-self-start m-3">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
            </div>
            <div>

            </div>
        </div>
        <div class = "card-dashboard mx-3 w-100 bg-white rounded shadow-sm custom-scrollbar">
            <div class = "header-card-dashboard d-flex justify-content-between border-bottom">
                <div class = "d-flex flex-column justify-content-center align-items-start ml-3">
                    <span class = "titulo-card-dashboard">Título card</span>
                    <span class = "subtitulo-card-dashboard">Subtítulo</span>
                </div>    
                <div class = "align-self-start m-3">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
            </div>
            <div>

            </div>
        </div>
        
    </div>

    <script type = "text/javascript" language = "JavaScript">
        function listar() {}

        function produtosEmAtraso(idFuncionario) {
            console.log("teste");
            $.get(URL + "/produtos-em-atraso/" + idFuncionario, function(data) {
                let resultado = "";
                if (typeof data == "string") data = $.parseJSON(data);
                data.forEach((linha) => {
                    resultado += 
                    "<tr>" +
                        "<td width = '50%' class = 'text-left px-2'>" + linha.produto + "</td>" +
                        "<td width = '25%' class = 'text-right pr-2'>" + linha.qtd + "</td>" +
                        "<td width = '25%' class = 'text-right pr-2'>" + linha.validade + "</td>" +
                    "</tr>";
                });
                document.getElementById("table-itens-em-atraso-dados").innerHTML = resultado;
                modal("itensEmAtrasoModal", 0);
            });
        }

    </script>
    @include("modals.itens_em_atraso_modal")
@endsection
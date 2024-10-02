@extends("layouts.app")

@section("content")
    <div class = "container-fluid h-100 px-3">
        <div class = "row">
            <table class = "w-100">
                <tr>
                    <td class = "w-100">
                        <h3 class = "col header-color mb-3" id = "titulo-tela">{{ $titulo }}</h3>
                    </td>
                    <td class = "ultima-atualizacao">
                        <span class = "custom-label-form">{{ $ultima_atualizacao }}</span>
                    </td>
                </tr>
            </table>
            <div id = "filtro-grid-by0" class = "input-group col-12 mb-3" data-table = "#table-dados">
                <input id = "busca" type = "text" class = "form-control form-control-lg" placeholder = "Procurar por..." aria-label = "Procurar por..." aria-describedby = "btn-filtro" />
                <div class = "input-group-append">
                    <button class = "btn btn-secondary btn-search-grid" type = "button" onclick = "listar()">
                        <i class = "my-icon fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class = "custom-table card">
            <div class = "table-header-scroll">
                <table>
                    <thead>
                        <tr class = "sortable-columns" for = "#table-dados">
                            <th width = "10%" class = "text-right">
                                <span>Código</span>
                            </th>
                            <th width = "30%">
                                <span>Nome</span>
                            </th>
                            <th width = "25%">
                                <span>Empresa</span>
                            </th>
                            <th width = "20%">
                                <span>Setor</span>
                            </th>
                            <th width = "15%" class = "text-center nao-ordena">
                                <span>Ações</span>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class = "table-body-scroll custom-scrollbar">
                <table id = "table-dados" class = "table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <button class = "btn btn-primary custom-fab" type = "button" onclick = "pessoa = new Pessoa(0)">
        <i class = "my-icon fas fa-plus"></i>
    </button>
    <script type = "text/javascript" language = "JavaScript">
        function listar(coluna) {
            $.get(URL + "/colaboradores/listar/", {
                filtro : document.getElementById("busca").value,
                tipo : document.getElementById("titulo-tela").innerHTML.charAt(0)
            }, function(data) {
                let resultado = "";
                if (typeof data == "string") data = $.parseJSON(data);
                data.forEach((linha) => {
                    resultado += "<tr>" +
                        "<td width = '10%' class = 'text-right'>" + linha.id.toString().padStart(4, "0") + "</td>" +
                        "<td width = '30%'>" + linha.nome + "</td>" +
                        "<td width = '25%'>" + linha.empresa + "</td>" +
                        "<td width = '20%'>" + linha.setor + "</td>" +
                        "<td class = 'text-center btn-table-action' width = '15%'>";
                    if (parseInt(linha.possui_retiradas)) resultado += "<i class = 'my-icon fa-light fa-file' title = 'Retiradas' onclick = 'retirada_pessoa(" + linha.id + ")'></i>";
                    resultado += "" +
                            "<i class = 'my-icon far fa-box'       title = 'Atribuir produto' onclick = 'atribuicao(false, " + linha.id + ")'></i>" +
                            "<i class = 'my-icon far fa-tshirt'    title = 'Atribuir grade'   onclick = 'atribuicao(true, " + linha.id + ")'></i>" +
                            "<i class = 'my-icon far fa-edit'      title = 'Editar'           onclick = 'pessoa = new Pessoa(" + linha.id + ")'></i>" +
                            "<i class = 'my-icon far fa-trash-alt' title = 'Excluir'          onclick = 'excluir(" + linha.id + ", " + '"/colaboradores"' + ")'></i>" +
                        "</td>" +
                    "</tr>";
                });
                document.getElementById("table-dados").innerHTML = resultado;
                ordenar(coluna);
            });
        }

        function retirada_pessoa(id_pessoa) {
            let req = {};
            ["inicio", "fim"].forEach((chave) => {
                req[chave] = "";
            });
            req.id_pessoa = id_pessoa;
            req.tipo = 'A';
            let link = document.createElement("a");
            link.href = URL + "/relatorios/retiradas?" + $.param(req);
            link.target = "_blank";
            link.click();
        }
    </script>

    @include("modals.atribuicoes_modal")
    @include("modals.retiradas_modal")
    @include("modals.supervisor_modal")
@endsection
@extends("layouts.app")

@section("content")
    <div class = "container-fluid h-100 px-3">
        <div class = "row">
            <table class = "w-100">
                <tr>
                    <td class = "w-100">
                        <h3 class = "col header-color mb-3">Colaboradores</h3>
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
                            <th width = "35%">
                                <span>Nome</span>
                            </th>
                            <th width = "25%">
                                <span>Empresa</span>
                            </th>
                            <th width = "20%">
                                <span>Setor</span>
                            </th>
                            <th width = "10%" class = "text-center nao-ordena">
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
        let pessoa_atribuindo, limite_maximo;

        function listar() {
            $.get(URL + "/colaboradores/listar", {
                filtro : document.getElementById("busca").value
            }, function(data) {
                let resultado = "";
                if (typeof data == "string") data = $.parseJSON(data);
                data.forEach((linha) => {
                    resultado += "<tr>" +
                        "<td width = '10%' class = 'text-right'>" + linha.id.toString().padStart(4, "0") + "</td>" +
                        "<td width = '35%'>" + linha.nome + "</td>" +
                        "<td width = '25%'>" + linha.empresa + "</td>" +
                        "<td width = '20%'>" + linha.setor + "</td>" +
                        "<td class = 'text-center btn-table-action' width = '10%'>" +
                            "<i class = 'my-icon far fa-hand-holding' title = 'Atribuir produtos' onclick = 'atribuicao(" + linha.id + ")'></i>" +
                            "<i class = 'my-icon far fa-edit'         title = 'Editar'            onclick = 'pessoa = new Pessoa(" + linha.id + ")'></i>" +
                            "<i class = 'my-icon far fa-trash-alt'    title = 'Excluir'           onclick = 'excluir(" + linha.id + ", " + '"/colaboradores"' + ")'></i>"
                        "</td>" +
                    "</tr>";
                });
                document.getElementById("table-dados").innerHTML = resultado;
                $($(".sortable-columns").children()[0]).trigger("click");
            });
        }

        function mostrar_atribuicoes() {
            $.get(URL + "/atribuicoes/mostrar/" + pessoa_atribuindo, function(data) {
                let resultado = "";
                let elRes = document.getElementById("table-atribuicoes");
                if (typeof data == "string") data = $.parseJSON(data);
                if (data.length) {
                    resultado += "<thead>" +
                        "<tr>" +
                            "<th>Referência</th>" +
                            "<th class = 'text-right'>Quantidade</th>" +
                            "<th>&nbsp;</th>" +
                        "</tr>" +
                    "</thead>" +
                    "<tbody>";
                    data.forEach((atribuicao) => {
                        resultado += "<tr>" +
                            "<td>" + atribuicao.referencia + "</td>" +
                            "<td class = 'text-right'>" + atribuicao.qtd + "</td>" +
                            "<td class = 'text-center'>" +
                                "<i class = 'my-icon far fa-trash-alt' title = 'Excluir' onclick = 'excluir_atribuicao(" + atribuicao.id + ")'></i>" +
                            "</td>" +
                        "</tr>";
                    });
                    resultado += "</tbody>";
                    elRes.parentElement.classList.add("pb-4");
                } else elRes.parentElement.classList.remove("pb-4");
                elRes.innerHTML = resultado;
            });
        }

        function atribuicao(id) {
            modal("atribuicaoModal", 0, function() {
                pessoa_atribuindo = id;
                $.get(URL + "/colaboradores/mostrar/" + id, function(data) {
                    if (typeof data == "string") data = $.parseJSON(data);
                    document.getElementById("atribuicaoModalLabel").innerHTML = data.nome.toUpperCase() + " - Atribuindo produtos";
                    document.getElementById("referencia").dataset.filter = id;
                    mostrar_atribuicoes();
                });
            });
        }

        function atualizaLimiteMaximo() {
            id_produto = document.getElementById("id_produto").value;
            if (id_produto) {
                $.get(URL + "/atribuicoes/ver-maximo/" + id_produto, function(maximo) {
                    limite_maximo = parseFloat(maximo);
                });
            }
        }

        function atribuir() {
            $.post(URL + "/atribuicoes/salvar", {
                _token : $("meta[name='csrf-token']").attr("content"),
                referencia : document.getElementById("referencia").value,
                fk : pessoa_atribuindo,
                tabela : "pessoas",
                qtd : document.getElementById("quantidade").value
            }, function(ret) {
                if (parseInt(ret)) {
                    document.getElementById("id_produto").value = "";
                    document.getElementById("referencia").value = "";
                    document.getElementById("quantidade").value = 1;
                    mostrar_atribuicoes();
                } else s_alert("Referência não encontrada");
            });
        }

        function excluir_atribuicao(_id) {
            Swal.fire({
                title: "Aviso",
                html : "Tem certeza que deseja excluir essa referência?",
                showDenyButton : true,
                confirmButtonText : "NÃO",
                confirmButtonColor : "rgb(31, 41, 55)",
                denyButtonText : "SIM"
            }).then((result) => {
                if (result.isDenied) {
                    $.post(URL + "/atribuicoes/excluir", {
                        _token : $("meta[name='csrf-token']").attr("content"),
                        id : _id
                    }, function() {
                        mostrar_atribuicoes();
                    });
                }
            });
        }
    </script>

    @include("modals.atribuicao_modal")
@endsection
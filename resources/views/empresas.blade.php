@extends("layouts.app")

@section("content")
    <style type = "text/css">
        .btn-table-action {
            position:absolute;
            right:22px;
            margin-top:-25px
        }

        .btn-table-action i {
            width:22px;
            margin:0px 5px
        }

        .btn-table-action:hover i:not(:hover) {
            color:#000 !important
        }

        .espacamento {
            margin-left:70rem
        }

        .impar {
            background:#EEE !important
        }

        .texto-tabela {
            color:#858796;
            text-decoration:none;
            background-color:transparent;
            padding-bottom:5px;
            padding-top:2px
        }

        .texto-tabela:hover {
            color:#000;
            background:#DDD !important
        }

        summary.texto-tabela {
            list-style:revert;
            padding-left:5px
        }

        summary.texto-tabela .fa-trash-alt {
            display:none
        }

        div.filho {
            padding-left:21px
        }

        .sem-filhos {
            padding-left:3px
        }

        details .sem-filhos {
            padding-left:23px
        }

        details .sem-filhos .fa-plus {
            display:none
        }
    </style>

    <div class = "container-fluid h-100 px-3">
        <div class = "row">
            <table class = "w-100">
                <tr>
                    <td class = "w-100">
                        <h3 class = "col header-color mb-3">Empresas</h3>
                    </td>
                    <td class = "ultima-atualizacao">
                        <span class = "custom-label-form">{{ $ultima_atualizacao }}</span>
                    </td>
                </tr>
            </table>
        </div>
        <div id = "principal" role = "main" class = "main"></div>
    </div>

    @if ($pode_criar_matriz)
        <button class = "btn btn-primary custom-fab" type = "button" onclick = "chamar_modal(0)">
            <i class = "my-icon fas fa-plus"></i>
        </button>
    @endif
    
    <script type = "text/javascript" language = "JavaScript">
        let emp_atual;

        function zebrar(invertido) {
            let inverter = function(param, executa) {
                if (executa) param = !param;
                return param;
            }

            let visiveis = new Array();
            Array.from(document.getElementsByClassName("texto-tabela")).forEach((el) => {
                el.classList.remove("impar");
                el.classList.remove("par");
            });
            Array.from(document.getElementsByClassName("texto-tabela")).forEach((el) => {
                if (
                    (el.parentElement.tagName != "DETAILS") || (el.parentElement.tagName == "DETAILS" && (
                        (el.tagName != "DIV") || (el.tagName == "DIV" && inverter(el.parentElement.open, invertido))
                    ))
                ) visiveis.push(el.id);
            });
            for (let i = 0; i < visiveis.length; i++) document.getElementById(visiveis[i]).classList.add(((i % 2 > 0) ? "im" : "") + "par");
        }

        function listar() {
            let linha = function(id, nome) {
                return "<summary class = 'texto-tabela' id = 'empresa-" + id + "' onclick = 'zebrar(true)'>" +
                    nome +
                    "<div class = 'btn-table-action'>" +
                        "<i title = 'Atribuir setores' class = 'espacamento my-icon far fa-layer-group' onclick = 'spe(" + id + ", event)'></i>" +
                        "<i title = 'Nova filial'      class = 'my-icon far fa-plus'                    onclick = 'criar_filial(" + id + ", event)'></i>" +
                        "<i title = 'Editar'           class = 'my-icon far fa-edit'                    onclick = 'chamar_modal(" + id + ", event)'></i>" +
                        "<i title = 'Excluir'          class = 'my-icon far fa-trash-alt'               onclick = 'excluir(" + id + ", " + '"/empresas"' + ", event)'></i>" +
                    "</div>" +
                "</summary>";
            }

            $.get(URL + "/empresas/listar", function(data) {
                if (typeof data == "string") data = $.parseJSON(data);
                let resultado = "";
                data.inicial.forEach((empresa) => {
                    resultado += "<details>" + linha(empresa.id, empresa.nome_fantasia) + "</details>";
                });
                document.querySelector("#principal").innerHTML = resultado;
                data.final.forEach((empresa) => {
                    if (empresa.id_matriz != 0) {
                        document.querySelector("#empresa-" + empresa.id_matriz).parentElement.innerHTML += "<details class = 'filho'>" + 
                            linha(empresa.id, empresa.nome_fantasia) +
                        "</details>";
                        if (!parseInt(data.matriz_editavel)) document.querySelector("#empresa-" + empresa.id_matriz + " .btn-table-action").style.visibility = "hidden";
                    } else document.querySelector("#principal").innerHTML += linha(empresa.id, empresa.nome_fantasia);
                });
                Array.from(document.querySelectorAll("summary.texto-tabela")).forEach((el) => {
                    if (!$($(el).parent()).find("details").length) $($(el).parent()).replaceWith("<div class = 'sem-filhos texto-tabela' id = '" + el.id + "'>" + $(el).html() + "</div>");
                });
                zebrar(false);
            });
        }

        function validar_cnpj(cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g,'');
            if (cnpj == '' || cnpj.length != 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
            let tamanho = cnpj.length - 2
            let numeros = cnpj.substring(0, tamanho);
            let digitos = cnpj.substring(tamanho);
            let soma = 0;
            let pos = tamanho - 7;
            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0)) return false;
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1)) return false;
            return true;
        }

        function formatar_cnpj(el) {
            el.classList.remove("invalido");
            let rawValue = el.value.replace(/\D/g, "");
            if (rawValue.length === 15 && rawValue.startsWith("0")) {
                let potentialCNPJ = rawValue.substring(1);
                if (validar_cnpj(potentialCNPJ)) rawValue = potentialCNPJ;
            }
            el.value  = rawValue.replace(/^(\d{2})(\d)/, '$1.$2') // Adiciona ponto após o segundo dígito
                                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3') // Adiciona ponto após o quinto dígito
                                .replace(/\.(\d{3})(\d)/, '.$1/$2') // Adiciona barra após o oitavo dígito
                                .replace(/(\d{4})(\d)/, '$1-$2') // Adiciona traço após o décimo segundo dígito
                                .replace(/(-\d{2})\d+?$/, '$1'); // Impede a entrada de mais de 14 dígitos
        }

        function validar() {
            limpar_invalido();
            let erro = "";

            let _cnpj = document.getElementById("cnpj");
            if (!_cnpj.value.length) {
                erro = "Preencha o campo";
                _cnpj.classList.add("invalido");
            }
            const aux = verifica_vazios(["nome_fantasia", "razao_social"], erro);
            erro = aux.erro;
            let alterou = aux.alterou;
            if (!erro && !validar_cnpj(_cnpj.value)) {
                erro = "CNPJ inválido";
                _cnpj.classList.add("invalido");
            }
            if (_cnpj.value != anteriores.cnpj) alterou = true;

            $.get(URL + "/empresas/consultar/", {
                cnpj : _cnpj.value.replace(/\D/g, "")
            }, function(data) {
                if (!erro && parseInt(data) && !parseInt(document.getElementById("id").value)) {
                    erro = "Já existe um registro com esse CNPJ";
                    _cnpj.classList.add("invalido");
                }
                if (!erro && !alterou) erro = "Altere pelo menos um campo para salvar";
                if (!erro) {
                    _cnpj.value = _cnpj.value.replace(/\D/g, "");
                    document.querySelector("#empresasModal form").submit();
                } else s_alert(erro);
            });
        }

        function chamar_modal(id, e) {
            if (e !== undefined) e.preventDefault();
            let titulo = id ? "Editando" : "Cadastrando";
            titulo += " empresa";
            document.getElementById("empresasModalLabel").innerHTML = titulo;
            if (id) {
                $.get(URL + "/empresas/mostrar/" + id, function(data) {
                    if (typeof data == "string") data = $.parseJSON(data);
                    ["id_matriz", "cnpj", "razao_social", "nome_fantasia"].forEach((_id) => {
                        document.getElementById(_id).value = data[_id];
                    });
                    if (parseInt(data.id_matriz)) document.getElementById("empresasModalLabel").innerHTML = "Editando filial";
                    modal("empresasModal", id);
                });
            } else modal("empresasModal", id);
        }

        function criar_filial(matriz, e) {
            e.preventDefault();
            document.getElementById("empresasModalLabel").innerHTML = "Criando filial";
            modal("empresasModal", 0, function() {
                document.getElementById("id_matriz").value = matriz;
            });
        }

        function excluir_spe(_id) {
            excluirMain(_id, "/empresas/setores", "Tem certeza que deseja excluir?", function() {
                mostrar_spe();
            });
        }

        function mostrar_spe(_id) {
            $.get(URL + "/empresas/setores/listar/" + _id, function(data) {
                let resultado = "";
                let elRes = document.getElementById("table-spe");
                if (typeof data == "string") data = $.parseJSON(data);
                if (data.length) {
                    resultado += "<thead>" +
                        "<tr>" +
                            "<th>Setor</th>" +
                            "<th>&nbsp;</th>" +
                        "</tr>" +
                    "</thead>" +
                    "<tbody>";
                    data.forEach((spe) => {
                        resultado += "<tr>" +
                            "<td>" + spe.descr + "</td>" +
                            "<td class = 'text-center'>" +
                                "<i class = 'my-icon far fa-trash-alt' title = 'Excluir' onclick = 'excluir_spe(" + spe.id + ")'></i>" +
                            "</td>" +
                        "</tr>";
                    });
                    resultado += "</tbody>";
                    elRes.parentElement.classList.add("pb-4");
                } else elRes.parentElement.classList.remove("pb-4");
                elRes.innerHTML = resultado;
            });
        }

        function spe(id, e) {
            e.preventDefault();
            emp_atual = id;
            modal("speModal", 0, function() {
                $.get(URL + "/empresas/mostrar/" + id, function(data) {
                    if (typeof data == "string") data = $.parseJSON(data);
                    document.getElementById("speModalLabel").innerHTML = data.nome_fantasia + " - Atribuindo setores";
                    mostrar_spe(id);
                });
            });
        }

        function criar_spe() {
            $.post(URL + "/empresas/setores/salvar", {
                _token : $("meta[name='csrf-token']").attr("content"),
                setor : document.getElementById("setor2").value,
                id_setor : document.getElementById("id_setor2").value,
                id_empresa : emp_atual
            }, function(ret) {
                ret = parseInt(ret);
                switch(ret) {
                    case 201:
                        document.getElementById("id_setor2").value = "";
                        document.getElementById("setor2").value = "";
                        mostrar_spe(emp_atual);
                        break;
                    case 403:
                        s_alert("Setor inválido");
                        break;
                    case 404:
                        s_alert("Setor não encontrado");
                        break;
                }
            });
        }
    </script>

    @include("modals.spe_modal")
    @include("modals.empresas_modal")
@endsection

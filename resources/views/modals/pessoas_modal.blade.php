
<!-- Modal -->
<div class = "modal fade" id = "pessoasModal" aria-labelledby = "pessoasModalLabel" aria-hidden = "true">
    <div class = "modal-dialog modal-lg" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <h6 class = "modal-title header-color" id = "pessoasModalLabel"></h6>
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span>
                </button>
            </div>
            <form action = "{{ config('app.root_url') }}/colaboradores/salvar" method = "POST" enctype = "multipart/form-data">
                <div class = "modal-body">
                    <div class = "container">
                        @csrf
                        <input id = "pessoa-id" name = "id" type = "hidden" />
                        <input name = "tipo" value = "{{ $tipo ?? '' }}" type = "hidden" />
                        <div class = "row py-4">
                            <div class = "user-pic" style = "scale:2">
                                <span class = "m-auto">
                                    @foreach(explode(" ", Auth::user()->name, 2) as $nome)
                                        {{ substr($nome, 0, 1) }}
                                    @endforeach
                                </span>
                            </div>
                        </div>
                        <div class = "row">
                            <div class = "col-6">
                                <label for = "nome" class = "custom-label-form">Nome: *</label>
                                <input id = "nome" name = "nome" class = "form-control" autocomplete = "off" type = "text" onkeyup = "contar_char(this, 64)" />
                                <span class = "custom-label-form tam-max"></span>
                            </div>
                            <div class = "col-6">
                                <button type = "button" class = "btn btn-target btn-target-black w-100 mt-4" onclick = "$(this).next().trigger('click')">Adicionar imagem</button>
                                <input type = "file" name = "foto" class = "d-none" />
                            </div>
                        </div>
                        <div class = "row">
                            <div class = "col-6">
                                <label for = "funcao" class = "custom-label-form">Função:</label>
                                <input id = "funcao" name = "funcao" class = "form-control" autocomplete = "off" type = "text" onkeyup = "contar_char(this, 64)" />
                                <span class = "custom-label-form tam-max"></span>
                            </div>
                            <div class = "col-6">
                                <label for = "admissao" class = "custom-label-form">Admissão:</label>
                                <input id = "admissao" name = "admissao" class = "form-control data" autocomplete = "off" type = "text" />
                            </div>
                        </div>
                        <div class = "row">
                            <div class = "col-11 pr-0 form-search">
                                <label for = "pessoa-empresa" class = "custom-label-form">Empresa: *</label>
                                <input id = "pessoa-empresa"
                                    class = "form-control autocomplete"
                                    data-input = "#pessoa-id_empresa"
                                    data-table = "empresas"
                                    data-column = "nome_fantasia"
                                    data-filter_col = ""
                                    data-filter = ""
                                    type = "text"
                                    autocomplete = "off"
                                />
                                <input id = "pessoa-id_empresa" name = "id_empresa" type = "hidden" />
                            </div>
                            <div class = "col-1 pt-4 d-flex align-items-center">
                                <a href = "{{ config('app.root_url') }}/empresas" title = "Cadastro de empresas" target = "_blank">
                                    <i class="fa-sharp fa-regular fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                        <div class = "row">
                            <div class = "col-6">
                                <label for = "cpf" class = "custom-label-form">CPF: *</label>
                                <input id = "cpf" name = "cpf" class = "form-control" autocomplete = "off" type = "text" onkeyup = "formatar_cpf(this)" />
                            </div>
                            <div class = "col-5 pr-0 form-search-2">
                                <label for = "pessoa-setor" class = "custom-label-form">Setor: *</label>
                                <input id = "pessoa-setor"
                                    class = "form-control autocomplete"
                                    data-input = "#pessoa-id_setor"
                                    data-table = "setores"
                                    data-column = "descr"
                                    data-filter_col = "cria_usuario"
                                    data-filter = ""
                                    type = "text"
                                    autocomplete = "off"
                                />
                                <input id = "pessoa-id_setor" name = "pessoa-id_setor" type = "hidden" onchange = "pessoa.toggle_user(parseInt(this.value))"/>
                            </div>
                            <div class = "col-1 pt-4 d-flex align-items-center">
                                <a href = "{{ config('app.root_url') }}/setores" title = "Cadastro de setores" target = "_blank">
                                    <i class="fa-sharp fa-regular fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                        <div class = "row usr-info">
                            <div class = "col-12">
                                <label for = "email" class = "custom-label-form">E-mail: *</label>
                                <input id = "email" name = "email" class = "form-control" autocomplete = "off" type = "text"/>
                            </div>
                        </div>
                        <div class = "row usr-info">
                            <div class = "col-12">
                                <label for = "password" class = "custom-label-form pessoa-senha"></label>
                                <input id = "password" name = "password" class = "form-control" autocomplete = "off" type = "password"/>
                            </div>
                        </div>
                        <div class = "row" id = "pes-info">
                            <div class = "col-12">
                                <label for = "senha" class = "custom-label-form pessoa-senha"></label>
                                <input id = "senha" name = "senha" class = "form-control" autocomplete = "off" type = "password" onkeyup = "numerico(this)" />
                            </div>
                        </div>
                        <div class = "row mb-3" style = "padding-top:5px">
                            <div class = "col-12">
                                <div class = "custom-control custom-switch">
                                    <input id = "supervisor" name = "supervisor" type = "hidden" />
                                    <input id = "supervisor-chk" class = "checkbox custom-control-input" type = "checkbox" onchange = "document.getElementById('supervisor').value = this.checked ? 1 : 0" />
                                    <label for = "supervisor-chk" class = "custom-control-label">Supervisor<label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "d-flex">
                    <button type = "button" class = "btn btn-target mx-auto my-4 mb-4 px-5" onclick = "pessoa.validar()">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
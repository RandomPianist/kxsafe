
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
            <form action = "{{ config('app.root_url') }}/colaboradores/salvar" method = "POST">
                <div class = "modal-body">
                    <div class = "container">
                        @csrf
                        <input id = "pessoa-id" name = "id" type = "hidden" />
                        <div class = "row">
                            <div class = "col-12">
                                <label for = "nome" class = "custom-label-form">Nome: *</label>
                                <input id = "nome" name = "nome" class = "form-control" autocomplete = "off" type = "text" onkeyup = "contar_char(this, 64)" />
                                <span class = "custom-label-form tam-max"></span>
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
                            <div class = "col-12 form-search">
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
                        </div>
                        <div class = "row">
                            <div class = "col-6">
                                <label for = "cpf" class = "custom-label-form">CPF:</label>
                                <input id = "cpf" name = "cpf" class = "form-control" autocomplete = "off" type = "text" onkeyup = "pessoa.formatar_cpf(this)" />
                            </div>
                            <div class = "col-6 form-search">
                                <label for = "setor" class = "custom-label-form">Setor: *</label>
                                <input id = "setor"
                                    class = "form-control autocomplete"
                                    data-input = "#id_setor"
                                    data-table = "setores"
                                    data-column = "descr"
                                    data-filter_col = ""
                                    data-filter = ""
                                    type = "text"
                                    autocomplete = "off"
                                />
                                <input id = "id_setor" name = "id_setor" type = "hidden" onchange = "pessoa.toggle_user(parseInt(this.value))"/>
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
                                <label id = "pessoa-senha" for = "password" class = "custom-label-form"></label>
                                <input id = "password" name = "password" class = "form-control" autocomplete = "off" type = "password"/>
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

<!-- Modal -->
<div class = "modal fade" id = "relatorioRetiradasModal" aria-labelledby = "relatorioRetiradasModalLabel" aria-hidden = "true">
    <div class = "modal-dialog modal-lg" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <h6 class = "modal-title header-color">Retiradas</h6>
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span>
                </button>
            </div>
            <form action = "{{ config('app.root_url') }}/relatorios/retiradas" method = "GET" target = "_blank">
                <div class = "modal-body">
                    <div class = "container">
                        <div class = "row">
                            <div class = "col-12 form-search">
                                <label for = "rel-pessoa" class = "custom-label-form">Colaborador:</label>
                                <input id = "rel-pessoa"
                                    name = "pessoa"
                                    class = "form-control autocomplete"
                                    data-input = "#rel-id_pessoa"
                                    data-table = "pessoas"
                                    data-column = "nome"
                                    data-filter_col = ""
                                    data-filter = ""
                                    type = "text"
                                    autocomplete = "off"
                                />
                                <input id = "rel-id_pessoa" name = "id_pessoa" type = "hidden" />
                            </div>
                        </div>
                        <div class = "row">
                            <div class = "col-6">
                                <label for = "rel-inicio2" class = "custom-label-form">Início:</label>
                                <input id = "rel-inicio2" name = "inicio" class = "form-control data" autocomplete = "off" type = "text" />
                            </div>
                            <div class = "col-6">
                                <label for = "rel-fim2" class = "custom-label-form">Fim:</label>
                                <input id = "rel-fim2" name = "fim" class = "form-control data" autocomplete = "off" type = "text" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "d-flex">
                    <button type = "button" class = "btn btn-target mx-auto mb-4 my-4 px-5" onclick = "relatorio.validar()">Visualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
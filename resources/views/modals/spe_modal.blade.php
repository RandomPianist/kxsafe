
<!-- Modal -->
<div class = "modal fade" id = "speModal" aria-labelledby = "speModalLabel" aria-hidden = "true">
    <div class = "modal-dialog modal-lg" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <h6 class = "modal-title header-color" id = "speModalLabel"></h6>
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span>
                </button>
            </div>
            <div class = "modal-body">
                <div class = "container">
                    <div class = "row pb-4">
                        <div class = "col-9" id = "div-produto">
                            <label for = "setor2" class = "custom-label-form">Setor: *</label>
                            <input id = "setor2"
                                class = "form-control autocomplete"
                                data-input = "#id_setor2"
                                data-table = "setores"
                                data-column = "descr"
                                data-filter_col = ""
                                data-filter = ""
                                type = "text"
                                autocomplete = "off"
                                style = "width:103%"
                            />
                            <input id = "id_setor2" name = "id_setor" type = "hidden"/>
                        </div>
                        <div class = "col-1 d-flex align-items-center pl-0 pt-3 j-end">
                            <a href = "{{ config('app.root_url') }}/setores" title = "Cadastro de setores" target = "_blank">
                                <i class="fa-sharp fa-regular fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                        <div class = "col-2 text-right">
                            <button type = "button" class = "btn btn-target mx-auto px-3 mt-4 w-100" onclick = "criar_spe()">+</button>
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-12">
                            <table id = "table-spe" class = "w-100" border = 1></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
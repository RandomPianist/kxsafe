
<!-- Modal -->
<div class = "modal fade" id = "atribuicaoModal" aria-labelledby = "atribuicaoModalLabel" aria-hidden = "true">
    <div class = "modal-dialog modal-xl" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <h6 class = "modal-title header-color" id = "atribuicaoModalLabel"></h6>
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span>
                </button>
            </div>
            <div class = "modal-body">
                <div class = "container">
                    <div class = "row pb-4">
                        <div class = "col-5 d-none" id = "div-referencia">
                            <label for = "referencia" class = "custom-label-form">Referência: *</label>
                            <input id = "referencia"
                                class = "form-control autocomplete w-108"
                                data-input = "#id_produto"
                                data-table = "produtos"
                                data-column = "referencia"
                                data-filter_col = ""
                                data-filter = ""
                                type = "text"
                                autocomplete = "off"
                            />
                            <input id = "id_produto" type = "hidden" onchange = "atualizaLimiteMaximo()" />
                        </div>
                        <div class = "col-5" id = "div-produto">
                            <label for = "produto" class = "custom-label-form">Produto: *</label>
                            <input id = "produto"
                                class = "form-control autocomplete w-108"
                                data-input = "#id_produto"
                                data-table = "produtos"
                                data-column = "descr"
                                data-filter_col = ""
                                data-filter = ""
                                type = "text"
                                autocomplete = "off"
                            />
                        </div>
                        <div class = "col-1 d-flex align-items-center pl-0 pt-3 j-end">
                            <a href = "{{ config('app.root_url') }}/produtos" title = "Cadastro de produtos" target = "_blank">
                                <i class="fa-sharp fa-regular fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                        <div class = "col-2">
                            <label for = "quantidade" class = "custom-label-form">Quantidade: *</label>
                            <input id = "quantidade" name = "quantidade" class = "form-control text-right" autocomplete = "off" type = "number" onkeyup = "$(this).trigger('change')" onchange = "limitar(this)" />
                        </div>
                        <div class = "col-2">
                            <label for = "validade" class = "custom-label-form">Validade em dias: *</label>
                            <input id = "validade" name = "validade" class = "form-control text-right" autocomplete = "off" type = "number" onkeyup = "$(this).trigger('change')" onchange = "limitar(this)" />
                        </div>
                        <div class = "col-2 text-right">
                            <button type = "button" class = "btn btn-target mx-auto px-3 mt-4 w-100" onclick = "atribuir()">+</button>
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-12">
                            <table id = "table-atribuicoes" class = "w-100" border = 1></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
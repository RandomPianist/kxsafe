@extends("layouts.rel")

@section("content")
    <div class = "report-header">
        <div class = "float-left">
            <img height = "75px" src = "{{ asset('img/logo.png') }}" />
        </div>
        <div class = "float-right">
            <ul class = "m-0">
                <li class = "text-right">
                    <h6 class = "m-0 fw-600">Retiradas</h6>
                </li>
                <li class = "text-right">
                    <h6 class = "m-0 traduzir">
                        @php
                            date_default_timezone_set("America/Sao_Paulo");
                            echo ucfirst(strftime("%A, %d de %B de %Y"));
                        @endphp
                    </h6>
                </li>
                <li class = "text-right">
                    @if ($criterios)
                        <h6 class = "m-0">Critérios:</h6>
                        <small>{{ $criterios }}</small>
                    @endif
                </li>
            </ul>
        </div>
    </div>
    <div class = "mt-2 mb-3 linha"></div>
    @foreach ($resultado AS $item)
        <h5>{{ $item["grupo"] }}</h5>
        @if ($tipo == 'A')
            <table class = "report-body table table-sm table-bordered table-striped px-5">
                <thead>
                    <tr class = "report-row">
                        <td width = "20%">Data</td>
                        @if ($item["quebra"] == "setor")
                            <td width = "25%">Produto</td>
                            <td width = "25%">Colaborador</td>
                        @else
                            <td width = "50%">Produto</td>
                        @endif
                        <td width = "10%" class = "text-right">Qtde.</td>
                        <td width = "20%" class = "text-right">Valor</td>
                    </tr>
                </thead>
            </table>
            <div class = "mb-3">
                <table class = "report-body table table-sm table-bordered table-striped">
                    <tbody>
                        @foreach ($item["retiradas"] as $retirada)
                            <tr class = "report-row">
                                <td width = "20%">{{ $retirada["data"] }}</td>
                                @if ($item["quebra"] == "setor")
                                    <td width = "25%">{{ $retirada["produto"] }}</td>
                                    <td width = "25%">{{ $retirada["pessoa"] }}</td>
                                @else
                                    <td width = "50%">{{ $retirada["produto"] }}</td>
                                @endif
                                
                                <td width = "10%" class = "text-right">{{ $retirada["qtd"] }}</td>
                                <td width = "20%" class = "text-right">R$ {{ number_format($retirada["valor"], 2, ",", ".") }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <h6>Quantidade total: {{ $item["total_qtd"] }}</h6>
        <h6>Valor total: R$ {{ number_format($item["total_valor"], 2, ",", ".") }}</h6>
        <div class = "line-div"></div>
    @endforeach
@endsection
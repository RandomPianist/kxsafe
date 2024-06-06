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
    <div class = "mt-2 mb-3" style = "border-top:solid #000 1px"></div>
    @foreach ($resultado AS $item)
        <h5>{{ $item["nome"] }}</h5>
        <table class = "report-body table table-sm table-bordered table-striped px-5">
            <thead>
                <tr class = "report-row">
                    <td width = "20%">Produto</td>
                    <td width = "20%">Máquina</td>
                    <td width = "20%">Data</td>
                    <td width = "40%">Observações</td>
                </tr>
            </thead>
        </table>
        <div class = "mb-3">
            <table class = "report-body table table-sm table-bordered table-striped">
                <tbody>
                    @foreach ($item["retiradas"] as $retirada)
                        <tr class = "report-row">
                            <td width = "20%">{{ $retirada["produto"] }}</td>
                            <td width = "20%">{{ $retirada["maquina"] }}</td>
                            <td width = "20%">{{ $retirada["data"] }}</td>
                            <td width = "40%">{{ $retirada["obs"] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class = "line-div"></div>
    @endforeach
@endsection
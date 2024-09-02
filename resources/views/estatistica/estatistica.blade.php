<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas Desfile da Mordomia</title>
    <style>
        .info-table td {
            padding: 10px 30px;
        }
        .info-table p {
            font-size: 14px;
            margin: 5px 0;
        }
        .info-table p:first-child {
            margin-top: 0;
        }
        .header {
            text-align: center;
            position: fixed;
            top: -15px;
            left: 0;
            right: 0;
            height: 125px;
            margin-bottom: 50px !important;
        }
        .header img {
            height: 125px;
        }
    </style>
</head>
<body style="width: 100% !important; margin: 0; padding: 0; margin-top: 120px; background-color: #faf4f2; font-family: Arial, sans-serif; text-align: center;">
    <div class="header">
        <img src="{{ public_path('uploads/viana-festas-logo.png') }}" alt="Header Image">
    </div>
    <div style="text-align: left !important; padding: 0px 50px;">
        <p style="font-size: 12px;"><b>Nº da Inscrições:</b> <span style="font-size: 12px;">{{ $data->total }}</span></p>
        <p style="font-size: 12px;"><b>Média de Idades:</b> <span style="font-size: 12px;">{{ $data->media }}</span></p>
        <p style="font-size: 12px;"><b>Moda de Idades:</b> <span style="font-size: 12px;">{{ $data->moda }}</span></p>
        <p style="font-size: 12px;"><b>Participante mais novo:</b> <span style="font-size: 12px;">{{ $data->youngest }}</span></p>
        <p style="font-size: 12px;"><b>Participante mais velho:</b> <span style="font-size: 12px;">{{ $data->oldest }}</span></p>
        {{-- <p style="font-size: 12px;"><b>Países Inscritos:</b> <span style="font-size: 12px;">{{ $data->countries }}</span></p>
        <p style="font-size: 12px;"><b>Top 3 Trajes:</b> <span style="font-size: 12px;">{{ $data->trajes }}</span></p>
        <p style="font-size: 12px;"><b>Distritos Participantes:</b> <span style="font-size: 12px;">{{ $data->distritos }}</span></p> --}}
        @if(!empty($data->trajes))
            <p style="font-size: 12px;"><b>Trajes dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->trajes as $traje)
                    <li>{{ $traje }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->countries))
            <p style="font-size: 12px;"><b>Países dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->countries as $country)
                    <li>{{ $country }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->distritos))
            <p style="font-size: 12px;"><b>Distritos dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->distritos as $distrito)
                    <li>{{ $distrito }}</li>
                @endforeach
            </ul>
        @endif

        @if(!empty($data->trajes))
            <p style="font-size: 12px;"><b>Trajes dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->trajes as $traje)
                    <li>{{ $traje }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->countries))
            <p style="font-size: 12px;"><b>Países dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->countries as $country)
                    <li>{{ $country }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->distritos))
            <p style="font-size: 12px;"><b>Distritos dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->distritos as $distrito)
                    <li>{{ $distrito }}</li>
                @endforeach
            </ul>
        @endif

        @if(!empty($data->trajes))
            <p style="font-size: 12px;"><b>Trajes dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->trajes as $traje)
                    <li>{{ $traje }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->countries))
            <p style="font-size: 12px;"><b>Países dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->countries as $country)
                    <li>{{ $country }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->distritos))
            <p style="font-size: 12px;"><b>Distritos dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->distritos as $distrito)
                    <li>{{ $distrito }}</li>
                @endforeach
            </ul>
        @endif

        @if(!empty($data->trajes))
            <p style="font-size: 12px;"><b>Trajes dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->trajes as $traje)
                    <li>{{ $traje }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->countries))
            <p style="font-size: 12px;"><b>Países dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->countries as $country)
                    <li>{{ $country }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($data->distritos))
            <p style="font-size: 12px;"><b>Distritos dos Participantes:</b></p>
            <ul style="font-size: 12px;">
                @foreach($data->distritos as $distrito)
                    <li>{{ $distrito }}</li>
                @endforeach
            </ul>
        @endif
                            
    </div>
</body>
</html>

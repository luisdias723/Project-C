<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Comprovativo Inscrição</title>
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
    </style>
</head>
<body style="width: 100% !important; margin: 0; padding: 0; background-color: #faf4f2; font-family: Arial, sans-serif; text-align: center;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: auto; background-color: #faf4f2;">
        <tr>
            <td>
                <img src="data:image/jpeg;base64,{{ $banner }}" alt="banner" style="width: 100%; height: auto;">
            </td>
        </tr>
        <tr>
            <td>
                <table role="presentation" style="width: 100%; margin-top: 5px;">
                    <tr>
                        <td style="width: 60%; padding-bottom: 20px; padding-left: 30px;">
                            <h1 style="font-size: 24px; margin: 0;">{{ $event['eventName'] }}</h1>
                            <p style="font-size: 12px; margin: 5px 0;"><b>Quadro:</b> {{ $data['board'] }}</p>
                            <p style="font-size: 16px; margin: 5px 0;">{{ $event['data'] }}</p>
                            <p style="font-size: 16px; margin: 5px 0;">{{ $event['hora'] }}</p>
                        </td>
                        <td style="width: 40%; text-align: right; vertical-align: top;">
                            <img src="{{ $qrcode }}" alt="qrcode" width="150" height="150" style="margin-top: 25px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <hr style="border: none; border-bottom: 1px solid #000;">
            </td>
        </tr>
        <tr>
            <td>
                @if(isset($data['participant_email']))
                    <h3 style="font-size: 18px; margin: 0; padding-left: 30px;">Informações dos participantes</h3>
                    <table role="presentation" class="info-table" style="width: 100%; text-align: left;">
                        <h5 style="font-size: 14px; margin: 0; padding-left: 30px;">Participante 1</h5>
                        <tr>
                            <td style="width: 40%;">
                                <p style="font-size: 12px;">Nº da inscrição:</p>
                                <p style="font-size: 12px;">Nome:</p>
                                <p style="font-size: 12px;">Data de Nascimento:</p>
                            </td>
                            <td style="width: 60%;">
                                <p style="font-size: 12px;">{{ $data['code'] }}</p>
                                <p style="font-size: 12px;">{{ $data['name'] }}</p>
                                <p style="font-size: 12px;">{{ $data['dt_nascimento'] }}</p>
                            </td>
                        </tr>
                    </table>
                    @foreach($data['participant_email'] as $index => $email)
                        <table role="presentation" class="info-table" style="width: 100%; text-align: left;">
                            <h5 style="font-size: 14px; margin: 0; padding-left: 30px;">Participante {{ $index + 2 }}</h5>
                            <tr>
                                <td style="width: 40%;">
                                    <p style="font-size: 12px;">Nº da inscrição:</p>
                                    <p style="font-size: 12px;">Nome:</p>
                                    <p style="font-size: 12px;">Data de Nascimento:</p>
                                </td>
                                <td style="width: 60%;">
                                    <p style="font-size: 12px;">{{ $data['code'] }}</p>
                                    <p style="font-size: 12px;">{{ $email['name'] }}</p>
                                    <p style="font-size: 12px;">{{ $email['dt_nascimento'] }}</p>
                                </td>
                            </tr>
                        </table>
                    @endforeach
                @else
                    <h3 style="font-size: 18px; margin: 0; padding-left: 30px;">Informações de participante</h3>
                    <table role="presentation" class="info-table" style="width: 100%; text-align: left;">
                        <tr>
                            <td style="width: 40%;">
                                <p style="font-size: 12px;">Nº da inscrição:</p>
                                <p style="font-size: 12px;">Nome:</p>
                                <p style="font-size: 12px;">Data de Nascimento:</p>
                            </td>
                            <td style="width: 60%;">
                                <p style="font-size: 12px;">{{ $data['code'] }}</p>
                                <p style="font-size: 12px;">{{ $data['name'] }}</p>
                                <p style="font-size: 12px;">{{ $data['dt_nascimento'] }}</p>
                            </td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <div style="padding-left: 30px; text-align: left;">
                    <p style="font-size: 14px; margin: 0; font-weight: bold;">Direções</p>
                    <p style="font-size: 12px; margin: 5px 0;">Alameda 5 de Outubro<br>Viana do Castelo, Portugal</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <hr style="border: none; border-bottom: 1px solid #000;">
            </td>
        </tr>
        <tr>
            <td>
                <div style="padding: 5px 20px; text-align: left;">
                    <p style="font-size: 14px; font-style: italic; margin: 0;"><b>Notas:</b></p>
                    <ul style="padding-left: 20px; margin-top: 5px;">
                        <li style="font-size: 10px; margin-bottom: 10px;">
                            Relembramos que:
                            <ul style="margin-top: 5px; padding-left: 20px;">
                                <li>Não é permitido o uso de unhas pintadas ou de gel, curtas ou pontiagudas, nem de maquilhagem.</li>
                                <li>As tatuagens têm de ser devidamente encobertas.</li>
                                <li>Os piercings têm de ser removidos e/ou devidamente cobertos.</li>
                            </ul>
                        </li>
                        <li style="font-size: 10px; margin-bottom: 10px;">Este documento serve de prova para a sua inscrição. Deve fazer-se acompanhar deste documento impresso ou de forma digital no dia do evento.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

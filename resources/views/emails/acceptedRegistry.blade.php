<?php
$path = public_path('/uploads/viana-festas-logo.png');
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

$pathqr = $qrcode;
$typeqr = pathinfo($pathqr, PATHINFO_EXTENSION);
$dataqr = file_get_contents($pathqr);
$base64qr = 'data:image/' . $typeqr . ';base64,' . base64_encode($dataqr);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>
<body style="width: 100% !important; margin: 0; padding: 0; background-color: #faf4f2; font-family: Arial, sans-serif; text-align: center;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: auto; padding: 20px; background-color: #faf4f2;">
        <tr>
            <td style="text-align: center;">
                <img src="<?php echo $base64 ?>" alt="Logo" style="width: 450px; margin: 20px 0;">
            </td>
        </tr>
        <tr>
            <td style="background-color: #FFFFFF; padding: 40px;">
                <h1 style="color: #000; font-size: 2.5em; margin-bottom: 20px; font-family: Arial, sans-serif;">INSCRIÇÃO APROVADA</h1>
                @if (!empty($obs))
                    <p style="font-size: 1.2em; margin: 10px 0;">A sua inscrição para o Desfile da Mordomia foi aprovada. No entanto existem algumas observações que terá de cumprir no dia do desfile. Nomeadamente:</p>
                    <?php echo $obs ?>
                @else
                    <p style="font-size: 1.2em; margin: 10px 0;">A sua inscrição para o Desfile da Mordomia foi aprovada.</p>
                @endif
                <p style="font-size: 1.2em; margin: 10px 0;">Em anexo segue o comprovativo da sua inscrição e deverá fazer-se acompanhar do mesmo, impresso ou em formato digital, assim como do seu documento de identificação.</p>
                <table role="presentation" style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="text-align: left;">
                            <p style="font-size: 1.2em; margin: 10px 0;">Desfile da Mordomia</p>
                            <p style="font-size: 1.2em; margin: 10px 0;">Dia: 2024-08-16</p>
                            <p style="font-size: 1.2em; margin: 10px 0;">Hora: 14h00m</p>
                        </td>
                        <td style="text-align: right;">
                            <img src="<?php echo $base64qr ?>" alt="QRCode" style="width: 150px; height: 150px;">
                        </td>
                    </tr>
                </table>
                <hr style="border: none; border-bottom: 1px solid #000; margin: 20px 0;">
                <h4 style="font-size: 1.5em; margin-bottom: 10px;">Detalhes do participante</h4>
                <table role="presentation" style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="width: 40%; padding: 10px; text-align: left; font-size: 1.2em;">
                            <p style="margin: 0;">Traje:</p>
                            <p style="margin: 0;">Nome:</p>
                            <p style="margin: 0;">Nº de Identificação:</p>
                            <p style="margin: 0;">Data de Nascimento:</p>
                        </td>
                        <td style="width: 60%; padding: 10px; text-align: left; font-size: 1.2em;">
                            <p style="margin: 0;"><?php echo $reg['traje'] ?></p>
                            <p style="margin: 0;"><?php echo $reg['name'] ?></p>
                            <p style="margin: 0;"><?php echo $reg['identification'] ?></p>
                            <p style="margin: 0;"><?php echo $reg['dt_nascimento'] ?> (<?php echo $reg['cur_age'] ?>)</p>
                        </td>
                    </tr>
                </table>
                <div style="padding-left: 20px; text-align: left; margin-top: 20px;">
                    <h4 style="font-size: 1.5em; margin-bottom: 10px;">Direções</h4>
                    <p style="font-size: 1.2em; margin: 0;">Antigo Governo Civil de Viana do Castelo</p>
                    <p style="font-size: 1.2em; margin: 0;">Rua da Bandeira</p>
                    <p style="font-size: 1.2em; margin: 0;">Viana do Castelo, Portugal</p>
                </div>
                <div style="padding-left: 20px; text-align: left; margin-top: 20px;">
                    <h4 style="font-size: 1.5em; margin-bottom: 10px;">Notas:</h4>
                    <ul style="font-size: 1.2em; padding-left: 20px;">
                        <li style="margin-bottom: 10px;"><b>Não é permitido o uso de unhas pintadas ou de gel, curtas ou pontiagudas, nem de maquilhagem</b></li>
                        <li style="margin-bottom: 10px;"><b>As tatuagens têm de ser devidamente encobertas</b></li>
                        <li style="margin-bottom: 10px;"><b>Os piercings têm de ser removidos e/ou devidamente cobertos.</b></li>
                    </ul>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; background-color: #000; text-align: center;">
                <p style="color: white;">&copy; {{ date('Y') }} Viana Festas. Todos os direitos reservados. Omelette made by <a style="text-decoration: underline; color: white;" href="https://www.hovo.pt" target="_blank">HOVO!</a></p>
            </td>
        </tr>
    </table>
</body>
</html>

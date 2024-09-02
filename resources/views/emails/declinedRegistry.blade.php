<?php
$path = public_path('/uploads/viana-festas-logo.png');
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
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
            <td style="background-color: #FFFFFF; padding: 40px; text-align: start;">
                <h1 style="color: #000; font-size: 2.5em; margin-bottom: 20px; text-align: center;">OBSERVAÇÕES À SUA INSCRIÇÃO</h1>
                <p style="font-size: 1.2em; margin: 10px 0;">A sua inscrição não cumpre os requisitos estabelecidos pela nossa organização.</p>
                @if (!empty($obs))
                    <p style="font-size: 1.2em; margin: 10px 0;">Deverá ter em atenção as seguintes observações e deverá retificar a sua inscrição:</p>
                    <?php echo $obs ?>
                @endif
                @if (!empty($link))
                    <p style="text-align: center;">
                        <a href="{{ $link }}" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 5px; font-size: 1.2em;">Editar Inscrição</a>
                    </p>
                @endif
                <div>
                    <h4 style="font-size: 16px; margin-bottom: 5px;">Notas:</h4>
                    <ul style="padding-left: 20px;">
                        <li style="padding-bottom: 10px;"><b>Não é permitido o uso de unhas pintadas ou de gel, curtas ou pontiagudas, nem de maquilhagem</b></li>
                        <li style="padding-bottom: 10px;"><b>As tatuagens têm de ser devidamente encobertas</b></li>
                        <li style="padding-bottom: 10px;"><b>Os piercings têm de ser removidos e/ou devidamente cobertos.</b></li>
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

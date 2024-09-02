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
            <td style="background-color: #FFFFFF; padding: 40px;">
                <h1 style="color: #000; font-size: 2.5em; margin-bottom: 20px; font-family: Arial, sans-serif;">INSCRIÇÃO SUBMETIDA</h1>
                <p style="font-size: 1.2em; margin: 10px 0; text-align: left;">Muito obrigado pela sua inscrição.</p>
                <p style="font-size: 1.2em; margin: 10px 0; text-align: left;">A sua inscrição irá agora ser analisada pela nossa equipa.</p>
                <p style="font-size: 1.2em; margin: 10px 0; text-align: left;">Caso necessite de retificações iremos entrar em contacto consigo o mais breve possível.</p>
                <p style="font-size: 1.2em; margin: 10px 0; text-align: left;">Após aprovada a sua inscrição irá receber no seu email o comprovativo da sua inscrição e deverá fazer-se acompanhar do mesmo, impresso ou em formato digital, assim como do seu documento de identificação.</p>
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

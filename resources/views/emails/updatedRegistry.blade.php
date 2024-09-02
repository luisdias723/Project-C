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
    <style>
        .body {
            width: 100% !important;
            margin: 0;
            padding: 0;
            background-color: #faf4f2;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
        }

        .header img {
            width: 450px;
            margin: 20px 0;
        }

        .main {
            background-color: #FFFFFF;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #000;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2em;
            margin: 10px 0;
        }

        .footer {
            padding: 10px 10px 10px 10px;
            background-color: #000;
            margin-top: auto;
            width: 100%;
        }

        .footer p {
            font-size: 0.8em;
            color: #fff;
        }

        .edit-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
        }

        .edit-link:hover {
            background-color: #910710;
            color: #cfcfcf;
        }

        .notes {
            text-align: start;
        }

        .notes>li {
            padding-bottom: 10px;
        }

        .direction {
            text-align: start;
        }

        .direction .titulo {
            font-size: 16px;
            font-size-adjust: none;
            padding-bottom: 5px;
        }

        .direction .morada {
            font-size: 11px;
            font-size-adjust: none;
            padding-bottom: 3px;
        }

        .main>p {
            text-align: start;
        }

        .primaryOrange {
            background-color: #F69679 !important;
            border-color: #F69679 !important;
            color: #fff !important;
        }

    </style>
</head>
<div class="body">
    <div class="container">
        <div class="header">
            <img src="<?php echo $base64?>" alt="Logo">
        </div>
        <div class="main">
            <h1>INSCRIÇÃO ATUALIZADA</h1>
            <p class="opensansName">Foram efetuadas alterações à inscrição já existente para o Desfile da Mordomia com o código <b>{{ $code }}</b>.</p>
            <p class="opensansName">É necessário proceder à validação dos dados da inscrição.</p>
            <p class="opensansName">Poderá aceder à inscrição através de URL</p>
            <a href="reserva" class="edit-link primaryOrange">Validar Inscrição</a>
            <div class="direction">
                <h4>Notas:</h4>
                <ul class="notes">
                    <li><b>Não é permitido o uso de unhas pintadas ou de gel, curtas ou pontiagudas, nem de maquilhagem</b></li>
                    <li><b>As tatuagens têm de ser devidamente encobertas</b></li>
                    <li><b>Os piercings têm de ser removidos e/ou devidamente cobertos.</b></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer">
        <p style="color: white;">&copy; {{ date('Y') }} Viana Festas. Todos os direitos reservados. Omelette made by <a style="text-decoration: underline; color: white;" href="https://www.hovo.pt" target="_blank">HOVO!</a></p>
    </div>
</div>

</html>


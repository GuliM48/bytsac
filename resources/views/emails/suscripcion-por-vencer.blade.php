<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripción por vencer</title>
</head>
<body>
    <h2>Estimado {{ $subscription->client->razon_social }}</h2>

    <p>Le informamos que su suscripción al plan <strong>{{ $subscription->plan->nombre }}</strong> está próxima a vencer.</p>

    <ul>
        <li><strong>Plan:</strong> {{ $subscription->plan->nombre }}</li>
        <li><strong>Fecha de vencimiento:</strong> {{ $subscription->fecha_fin->format('d/m/Y') }}</li>
    </ul>

    <p>Para renovar su suscripción, por favor contacte a nuestro equipo comercial o ingrese a la plataforma.</p>

    <p>Saludos cordiales,<br>
    <strong>BYTSAC Technology</strong></p>
</body>
</html>

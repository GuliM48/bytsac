<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso comercial - Suscripción por vencer</title>
</head>
<body>
    <h2>Aviso al equipo comercial</h2>

    <p>El siguiente cliente tiene una suscripción próxima a vencer:</p>

    <ul>
        <li><strong>Cliente:</strong> {{ $subscription->client->razon_social }}</li>
        <li><strong>RUC:</strong> {{ $subscription->client->ruc }}</li>
        <li><strong>Contacto:</strong> {{ $subscription->client->email }}</li>
        <li><strong>Plan:</strong> {{ $subscription->plan->nombre }}</li>
        <li><strong>Fecha de vencimiento:</strong> {{ $subscription->fecha_fin->format('d/m/Y') }}</li>
    </ul>

    <p>Por favor contacte al cliente para coordinar la renovación.</p>

    <p>Saludos,<br>
    <strong>Sistema de Suscripciones - BYTSAC</strong></p>
</body>
</html>

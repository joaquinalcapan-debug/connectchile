<?php
require 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos
    $nombre    = $_POST['nombre']    ?? '';
    $email     = $_POST['email']     ?? '';
    $telefono  = $_POST['telefono']  ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $coords    = $_POST['coordenadas'] ?? '';
    $localidad = $_POST['localidad'] ?? '';
    $plan      = $_POST['plan']      ?? '';

    // Guardar en BD
    $stmt = $pdo->prepare("INSERT INTO factibilidad (nombre, email, telefono, direccion, coordenadas, localidad, plan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $telefono, $direccion, $coords, $localidad, $plan]);

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        /* ----------  CONFIG SMTP (igual que soporte)  ---------- */
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'joaquinllancapan48@gmail.com';
        $mail->Password   = 'gusgwvuggbjxnipc';   // app-password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = 465;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        /* ----------  MENSAJE  ---------- */
        $mail->setFrom('joaquinllancapan48@gmail.com', 'Connect Chile');
        $mail->addAddress('joaquinalcapan@gmail.com');
        $mail->addReplyTo($email, $nombre);

        $mail->isHTML(false);
        $mail->Subject = 'Nueva solicitud de factibilidad';
        $mail->Body    = "Nombre: $nombre\n"
                       . "Email: $email\n"
                       . "Teléfono: $telefono\n"
                       . "Dirección: $direccion\n"
                       . "Coordenadas: $coords\n"
                       . "Localidad: $localidad\n"
                       . "Plan: $plan";

        $mail->send();
        echo 'Solicitud enviada correctamente.';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'No se pudo enviar el correo: ', $mail->ErrorInfo;
        exit;
    }
} else {
    echo 'Método no permitido.';
}
?>
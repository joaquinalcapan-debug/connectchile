<?php
require 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = $_POST['nombre']   ?? '';
    $email    = $_POST['email']    ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $mensaje  = $_POST['mensaje']  ?? '';

    // Guardar en BD
    $stmt = $pdo->prepare("INSERT INTO soporte (nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $telefono, $mensaje]);

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        /* ----------  CONFIG SMTP  ---------- */
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'joaquinllancapan48@gmail.com';
        $mail->Password   = 'gusgwvuggbjxnipc';               // app-password, 16 chars sin espacios
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // SSL
        $mail->Port       = 465;

        // forzar TLS 1.2
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
        $mail->Subject = 'Nueva consulta de soporte';
        $mail->Body    = "Nombre: $nombre\n"
                       . "Email: $email\n"
                       . "Teléfono: $telefono\n"
                       . "Mensaje:\n$mensaje";

        $mail->send();
        echo 'Correo enviado correctamente.';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'No se pudo enviar el correo: ', $mail->ErrorInfo;
        exit;
    }
} else {
    echo 'Método no permitido.';
}
?>
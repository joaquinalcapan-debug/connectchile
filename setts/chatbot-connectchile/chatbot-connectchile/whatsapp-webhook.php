<?php
/**
 * ============================================
 * WHATSAPP BUSINESS API WEBHOOK
 * Connect Chile S.A.
 * ============================================
 * 
 * Este archivo maneja:
 * - Verificación del webhook (GET)
 * - Recepción de mensajes (POST)
 * - Respuestas automáticas con IA
 */

// Configuración - REEMPLAZAR CON TUS DATOS
$config = [
    'verify_token' => 'connectchile_webhook_token_2024', // Cambiar por uno seguro
    'phone_number_id' => 'TU_PHONE_NUMBER_ID', // De Meta Business
    'access_token' => 'TU_ACCESS_TOKEN', // De Meta Business
    'api_version' => 'v18.0',
];

header('Content-Type: application/json');

// ===== VERIFICACIÓN DEL WEBHOOK (GET) =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    if ($mode === 'subscribe' && $token === $config['verify_token']) {
        http_response_code(200);
        echo $challenge;
        error_log('Webhook verificado exitosamente');
        exit;
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Verificación fallida']);
        exit;
    }
}

// ===== RECEPCIÓN DE MENSAJES (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debugging
    error_log('Webhook recibido: ' . json_encode($input));
    
    // Procesar mensajes
    if (isset($input['entry'][0]['changes'][0]['value']['messages'])) {
        $messages = $input['entry'][0]['changes'][0]['value']['messages'];
        
        foreach ($messages as $message) {
            processMessage($message, $config);
        }
    }
    
    // Responder con éxito
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    exit;
}

/**
 * Procesar mensaje recibido
 */
function processMessage($message, $config) {
    $from = $message['from']; // Número del remitente
    $messageId = $message['id'];
    $messageType = $message['type'];
    
    // Obtener contenido del mensaje
    $content = '';
    if ($messageType === 'text') {
        $content = $message['text']['body'];
    } elseif ($messageType === 'button') {
        $content = $message['button']['text'];
    }
    
    error_log("Mensaje de {$from}: {$content}");
    
    // Generar respuesta con IA
    $response = generateResponse($content, $from);
    
    // Enviar respuesta
    sendWhatsAppMessage($from, $response, $config);
}

/**
 * Generar respuesta (IA local o OpenAI)
 */
function generateResponse($message, $from) {
    $lowerMessage = mb_strtolower($message, 'UTF-8');
    
    // Respuestas predefinidas
    $responses = [
        'hola' => "¡Hola! 👋 Bienvenido a Connect Chile. Soy tu asistente virtual. ¿En qué puedo ayudarte?\n\n1️⃣ Ver planes de internet\n2️⃣ Consultar cobertura\n3️⃣ Solicitar factibilidad\n4️⃣ Soporte técnico\n5️⃣ Pagar cuenta",
        
        'plan' => "Nuestros planes de internet:\n\n📶 15 Mb - $20.990/mes\n📶 25 Mb - $35.990/mes\n📶 35 Mb - $45.990/mes\n\n✅ Todos con IVA incluido\n✅ Transferencia ilimitada\n✅ Instalación desde $20.000\n\n¿Te gustaría solicitar factibilidad?",
        
        'cobertura' => "Tenemos cobertura en:\n📍 Panguipulli\n📍 Choshuenco\n📍 Neltume\n📍 Coñaripe\n📍 Lago Ranco\n\nPara confirmar tu dirección específica, solicita una factibilidad técnica gratuita. ¿Deseas que te ayude?",
        
        'factibilidad' => "Perfecto, te ayudo con la solicitud de factibilidad. 📝\n\nPuedes completar el formulario en:\n👉 connectchile.cl/factibilidad\n\nO darme tu dirección y te contactaremos en menos de 24 horas.",
        
        'soporte' => "Lamento que tengas problemas. 🔧\n\nIntenta estos pasos:\n1️⃣ Reinicia tu router (10 segundos)\n2️⃣ Verifica cables\n3️⃣ Espera 2 minutos\n\n¿El problema persiste? Te crearé un ticket.",
        
        'pago' => "Para pagar tu cuenta:\n\n💳 Portal de clientes:\n👉 connectchile.cl/cliente\n\n📱 También puedes transferir o pagar en nuestra oficina.\n\n¿Necesitas ayuda para acceder?",
        
        'contacto' => "📞 Nuestros contactos:\n\nWhatsApp: +56 9 9991 8468\nTeléfono: +56 9 9991 8468\nEmail: contacto@connectchile.cl\n\n📍 O'higgins #307, Panguipulli\n🕐 Lun-Vie: 10:30-17:30 hrs",
        
        'gracias' => "¡De nada! 😊 Estoy aquí para lo que necesites. ¡Que tengas un excelente día!",
        
        'adios' => "¡Hasta luego! 👋 No dudes en escribirnos cuando necesites. ¡Que tengas un buen día!"
    ];
    
    // Buscar coincidencia
    foreach ($responses as $keyword => $response) {
        if (strpos($lowerMessage, $keyword) !== false) {
            return $response;
        }
    }
    
    // Respuesta por defecto
    return "Entiendo. Para ayudarte mejor, elige una opción:\n\n1️⃣ Planes de internet\n2️⃣ Cobertura\n3️⃣ Factibilidad\n4️⃣ Soporte\n5️⃣ Contacto\n\nO escribe tu consulta y te ayudaré.";
}

/**
 * Enviar mensaje por WhatsApp Business API
 */
function sendWhatsAppMessage($to, $message, $config) {
    $url = "https://graph.facebook.com/{$config['api_version']}/{$config['phone_number_id']}/messages";
    
    $data = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $message
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['access_token']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        error_log("Mensaje enviado exitosamente a {$to}");
    } else {
        error_log("Error enviando mensaje: {$response}");
    }
    
    return $httpCode === 200;
}

// Método no permitido
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);

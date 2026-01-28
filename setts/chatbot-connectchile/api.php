<?php
/**
 * ============================================
 * CHATBOT WHATSAPP + IA - CONNECT CHILE
 * API Backend
 * ============================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener datos de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Mensaje requerido']);
    exit;
}

$message = trim($input['message']);
$context = $input['context'] ?? [];
$sessionId = $input['sessionId'] ?? uniqid('session_');

// Clase principal del Chatbot
class ConnectChileChatbotAPI {
    
    // Configuración de OpenAI (reemplazar con tu API key)
    private $openaiApiKey = '';
    private $useOpenAI = false; // Cambiar a true cuando tengas API key
    
    // Base de conocimientos de Connect Chile
    private $knowledgeBase = [
        'planes' => [
            '15mb' => [
                'nombre' => 'Plan 15 Mb',
                'precio' => '$20.990',
                'velocidad' => '15 Mbps',
                'caracteristicas' => ['Transferencia ilimitada', 'IVA incluido']
            ],
            '25mb' => [
                'nombre' => 'Plan 25 Mb',
                'precio' => '$35.990',
                'velocidad' => '25 Mbps',
                'caracteristicas' => ['Transferencia ilimitada', 'IVA incluido']
            ],
            '35mb' => [
                'nombre' => 'Plan 35 Mb',
                'precio' => '$45.990',
                'velocidad' => '35 Mbps',
                'caracteristicas' => ['Transferencia ilimitada', 'IVA incluido']
            ]
        ],
        'cobertura' => [
            'zonas' => ['Panguipulli', 'Choshuenco', 'Neltume', 'Coñaripe', 'Lago Ranco'],
            'mensaje' => 'Tenemos cobertura en Panguipulli y alrededores. Solicita una factibilidad técnica gratuita para confirmar tu dirección específica.'
        ],
        'instalacion' => [
            'costo' => '$20.000',
            'zona' => 'Panguipulli y alrededores',
            'nota' => 'El valor final debe ser confirmado con factibilidad técnica'
        ],
        'horario' => [
            'atencion' => 'Lunes a Viernes de 10:30 a 17:30 hrs',
            'direccion' => 'O\'higgins #307, Panguipulli'
        ],
        'contacto' => [
            'telefono' => '+56 9 9991 8468',
            'whatsapp' => '+56 9 9991 8468',
            'email' => 'contacto@connectchile.cl'
        ]
    ];

    /**
     * Procesar mensaje del usuario
     */
    public function processMessage($message, $context = [], $sessionId = '') {
        $lowerMessage = mb_strtolower($message, 'UTF-8');
        
        // Detectar intención
        $intent = $this->detectIntent($lowerMessage);
        
        // Generar respuesta
        if ($this->useOpenAI && !empty($this->openaiApiKey)) {
            $response = $this->getOpenAIResponse($message, $context);
        } else {
            $response = $this->getLocalResponse($intent, $lowerMessage, $context);
        }
        
        // Actualizar contexto
        $newContext = $this->updateContext($context, $intent, $message);
        
        // Determinar si hay alguna acción adicional
        $action = $this->determineAction($intent, $lowerMessage);
        
        return [
            'success' => true,
            'response' => $response,
            'context' => $newContext,
            'intent' => $intent,
            'action' => $action['action'] ?? null,
            'actionData' => $action['data'] ?? null
        ];
    }

    /**
     * Detectar intención del usuario
     */
    private function detectIntent($message) {
        $intents = [
            'saludo' => ['hola', 'buenos dias', 'buenas tardes', 'buenas noches', 'hey', 'saludos'],
            'despedida' => ['adios', 'chao', 'hasta luego', 'nos vemos', 'gracias', 'muchas gracias'],
            'planes' => ['plan', 'planes', 'precio', 'precios', 'costo', 'cuanto cuesta', 'valor', 'megas', 'mb', 'velocidad'],
            'cobertura' => ['cobertura', 'llegan', 'zona', 'sector', 'comuna', 'panguipulli', 'choshuenco', 'neltume', 'coñaripe'],
            'factibilidad' => ['factibilidad', 'instalar', 'instalacion', 'evaluacion', 'tecnica', 'disponible'],
            'soporte' => ['soporte', 'ayuda', 'problema', 'falla', 'no funciona', 'lento', 'cortado', 'sin internet'],
            'pago' => ['pagar', 'pago', 'cuenta', 'factura', 'abonar', 'deuda'],
            'contacto' => ['contacto', 'telefono', 'whatsapp', 'llamar', 'email', 'correo', 'hablar'],
            'horario' => ['horario', 'hora', 'atencion', 'abierto', 'cerrado'],
            'instalacion' => ['instalacion', 'instalar', 'poner', 'colocar']
        ];
        
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }
        
        return 'general';
    }

    /**
     * Generar respuesta local
     */
    private function getLocalResponse($intent, $message, $context) {
        $responses = [
            'saludo' => [
                '¡Hola! 👋 Bienvenido a Connect Chile. Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?',
                '¡Buenos días! 😊 ¿Cómo puedo asistirte con tu conexión de internet?',
                '¡Hola! Soy el asistente virtual de Connect Chile. ¿Qué necesitas?'
            ],
            
            'despedida' => [
                '¡Gracias por contactarnos! 👋 Que tengas un excelente día.',
                '¡Hasta luego! 😊 No dudes en escribirnos si necesitas algo más.',
                '¡Fue un gusto ayudarte! 🙌'
            ],
            
            'planes' => $this->getPlanesResponse($message),
            
            'cobertura' => [
                'Tenemos cobertura en Panguipulli y alrededores: Choshuenco, Neltume, Coñaripe y Lago Ranco. 🌐\n\nPara confirmar tu dirección específica, te recomiendo solicitar una factibilidad técnica gratuita. ¿Deseas que te ayude con eso?',
                '¡Sí! Llegamos a varias zonas de la Región de Los Ríos. 📍\n\nPara verificar tu dirección exacta, puedes solicitar una evaluación técnica sin costo. ¿Te gustaría hacerlo?'
            ],
            
            'factibilidad' => [
                'Perfecto, te ayudo con la solicitud de factibilidad. 📝\n\nPuedes completar el formulario en nuestra página web o darme tu dirección y te contactaremos.\n\n👉 <a href="inicio_de_factibilidad.html" target="_blank">Solicitar Factibilidad</a>',
                'Excelente decisión. La factibilidad técnica es gratuita y sin compromiso. ✅\n\n¿Cuál es tu dirección completa?'
            ],
            
            'soporte' => [
                'Lamento que tengas problemas. 🔧 Vamos a solucionarlo.\n\nPrimero, intenta estos pasos:\n1️⃣ Reinicia tu router (desconéctalo 10 segundos)\n2️⃣ Verifica que los cables estén bien conectados\n3️⃣ Espera 2 minutos y vuelve a conectar\n\n¿El problema persiste?',
                'Entiendo tu frustración. Vamos a ayudarte. 💪\n\n¿Podrías describirme mejor el problema? Por ejemplo:\n- ¿No tienes internet en absoluto?\n- ¿Está muy lento?\n- ¿Se corta constantemente?'
            ],
            
            'pago' => [
                'Puedes pagar tu cuenta de varias formas: 💳\n\n1️⃣ Portal de clientes: <a href="cliente.html" target="_blank">Acceder aquí</a>\n2️⃣ Transferencia bancaria\n3️⃣ Efectivo en nuestras oficinas\n\n¿Necesitas ayuda para acceder al portal?',
                'Para pagar tu cuenta, ingresa al portal de clientes con tu usuario y contraseña. 🔐\n\n👉 <a href="cliente.html" target="_blank">Portal de Pagos</a>\n\nSi olvidaste tu contraseña, puedes recuperarla desde el mismo portal.'
            ],
            
            'contacto' => [
                'Puedes contactarnos de estas formas: 📞\n\n📱 WhatsApp: +56 9 9991 8468\n☎️ Teléfono: +56 9 9991 8468\n📧 Email: contacto@connectchile.cl\n📍 Dirección: O\'higgins #307, Panguipulli\n\nHorario: Lunes a Viernes 10:30 - 17:30 hrs',
                '¡Claro! Estamos aquí para ayudarte. 😊\n\n📱 WhatsApp: +56 9 9991 8468\n☎️ Teléfono: +56 9 9991 8468\n\nHorario de atención: Lunes a Viernes de 10:30 a 17:30 hrs'
            ],
            
            'horario' => [
                'Nuestro horario de atención es:\n\n🕐 Lunes a Viernes: 10:30 - 17:30 hrs\n📍 O\'higgins #307, Panguipulli\n\nTambién puedes contactarnos por WhatsApp en cualquier momento.',
                'Estamos disponibles de lunes a viernes de 10:30 a 17:30 horas. 🕐\n\nPero nuestro WhatsApp está abierto 24/7 para tus consultas. 📱'
            ],
            
            'instalacion' => [
                'El costo de instalación en Panguipulli y alrededores es de $20.000. 💰\n\n⚠️ El valor final debe ser confirmado con la factibilidad técnica, ya que depende de la distancia y complejidad de la instalación.\n\n¿Te gustaría solicitar una evaluación?',
                'La instalación tiene un costo base de $20.000 en Panguipulli. 🔧\n\nEste valor puede variar según la factibilidad técnica de tu ubicación. Solicita una evaluación gratuita para conocer el precio exacto.'
            ],
            
            'general' => [
                'Entiendo. Para ayudarte mejor, ¿podrías darme más detalles? 🤔\n\nTambién puedes:\n• Ver nuestros <a href="inicio2.html#planes" target="_blank">planes de internet</a>\n• Solicitar <a href="inicio_de_factibilidad.html" target="_blank">factibilidad técnica</a>\n• Contactarnos por WhatsApp: +56 9 9991 8468',
                'Quiero asegurarme de entenderte bien. 💭\n\n¿Podrías reformular tu pregunta? O si prefieres, habla directamente con nosotros por WhatsApp haciendo clic en el botón verde de abajo.',
                'Disculpa, no entendí bien. 😅\n\nTe sugiero:\n1️⃣ Elegir una opción del menú principal\n2️⃣ Ser más específico con tu consulta\n3️⃣ O escribirnos directamente por WhatsApp'
            ]
        ];
        
        // Seleccionar respuesta aleatoria del intent
        $options = $responses[$intent] ?? $responses['general'];
        if (is_array($options)) {
            return $options[array_rand($options)];
        }
        return $options;
    }

    /**
     * Respuesta específica para planes
     */
    private function getPlanesResponse($message) {
        // Detectar plan específico
        if (strpos($message, '15') !== false) {
            return 'El Plan 15 Mb cuesta $20.990 mensual (IVA incluido). Incluye:\n\n✅ 15 Mbps de velocidad\n✅ Transferencia ilimitada\n✅ Sin costos ocultos\n\nCosto de instalación: $20.000 (sujeto a factibilidad). ¿Te gustaría solicitar una evaluación?';
        }
        if (strpos($message, '25') !== false) {
            return 'El Plan 25 Mb cuesta $35.990 mensual (IVA incluido). Incluye:\n\n✅ 25 Mbps de velocidad\n✅ Transferencia ilimitada\n✅ Ideal para familias\n\nCosto de instalación: $20.000 (sujeto a factibilidad). ¿Te interesa?';
        }
        if (strpos($message, '35') !== false) {
            return 'El Plan 35 Mb cuesta $45.990 mensual (IVA incluido). Incluye:\n\n✅ 35 Mbps de velocidad\n✅ Transferencia ilimitada\n✅ Perfecto para home office y streaming\n\nCosto de instalación: $20.000 (sujeto a factibilidad). ¿Quieres más información?';
        }
        
        // Respuesta general de planes
        return 'Tenemos tres planes de internet disponibles: 🌐\n\n📶 Plan 15 Mb - $20.990/mes\n📶 Plan 25 Mb - $35.990/mes\n📶 Plan 35 Mb - $45.990/mes\n\nTodos incluyen:\n✅ Transferencia ilimitada\n✅ IVA incluido\n✅ Soporte técnico\n\nCosto de instalación desde $20.000\n\n¿Cuál te interesa más?';
    }

    /**
     * Obtener respuesta de OpenAI
     */
    private function getOpenAIResponse($message, $context) {
        $systemPrompt = "Eres el asistente virtual de Connect Chile S.A., un proveedor de internet inalámbrico para zonas rurales y urbanas en Panguipulli, Chile.\n\n";
        $systemPrompt .= "INFORMACIÓN DE LA EMPRESA:\n";
        $systemPrompt .= "- Planes: 15Mb ($20.990), 25Mb ($35.990), 35Mb ($45.990) - todos con IVA incluido y transferencia ilimitada\n";
        $systemPrompt .= "- Cobertura: Panguipulli, Choshuenco, Neltume, Coñaripe, Lago Ranco\n";
        $systemPrompt .= "- Instalación: $20.000 base (sujeto a factibilidad técnica)\n";
        $systemPrompt .= "- Horario: Lunes a Viernes 10:30-17:30 hrs\n";
        $systemPrompt .= "- Dirección: O'higgins #307, Panguipulli\n";
        $systemPrompt .= "- Contacto: +56 9 9991 8468\n\n";
        $systemPrompt .= "DEBES:\n";
        $systemPrompt .= "- Responder de forma amable, profesional y concisa\n";
        $systemPrompt .= "- Usar emojis ocasionalmente\n";
        $systemPrompt .= "- Si no sabes algo, sugerir contactar por WhatsApp\n";
        $systemPrompt .= "- Responder en español de Chile\n";
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 300,
            'temperature' => 0.7
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        // Fallback si falla OpenAI
        return $this->getLocalResponse('general', $message, $context);
    }

    /**
     * Actualizar contexto de la conversación
     */
    private function updateContext($context, $intent, $message) {
        $context['lastIntent'] = $intent;
        $context['lastMessage'] = $message;
        $context['messageCount'] = ($context['messageCount'] ?? 0) + 1;
        
        // Guardar información específica
        if ($intent === 'planes') {
            if (strpos($message, '15') !== false) $context['interestedPlan'] = '15mb';
            if (strpos($message, '25') !== false) $context['interestedPlan'] = '25mb';
            if (strpos($message, '35') !== false) $context['interestedPlan'] = '35mb';
        }
        
        return $context;
    }

    /**
     * Determinar si hay acción adicional
     */
    private function determineAction($intent, $message) {
        // Sugerir WhatsApp después de varios mensajes
        if (strpos($message, 'hablar') !== false || strpos($message, 'ejecutivo') !== false) {
            return [
                'action' => 'redirect',
                'data' => ['url' => 'https://wa.me/56999918468']
            ];
        }
        
        return ['action' => null, 'data' => null];
    }
}

// Procesar la solicitud
$bot = new ConnectChileChatbotAPI();
$result = $bot->processMessage($message, $context, $sessionId);

echo json_encode($result);

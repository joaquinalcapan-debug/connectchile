<?php
/**
 * ============================================
 * CONFIGURACIÓN DEL CHATBOT
 * Connect Chile S.A.
 * ============================================
 */

return [
    // Información de la empresa
    'business' => [
        'name' => 'Connect Chile S.A.',
        'whatsapp' => '+56999918468',
        'phone' => '+56999918468',
        'email' => 'contacto@connectchile.cl',
        'address' => "O'higgins #307, Panguipulli",
        'hours' => 'Lunes a Viernes 10:30-17:30 hrs',
    ],
    
    // Planes de internet
    'planes' => [
        '15mb' => [
            'name' => 'Plan 15 Mb',
            'speed' => '15 Mbps',
            'price' => '$20.990',
            'features' => ['Transferencia ilimitada', 'IVA incluido'],
        ],
        '25mb' => [
            'name' => 'Plan 25 Mb',
            'speed' => '25 Mbps',
            'price' => '$35.990',
            'features' => ['Transferencia ilimitada', 'IVA incluido'],
        ],
        '35mb' => [
            'name' => 'Plan 35 Mb',
            'speed' => '35 Mbps',
            'price' => '$45.990',
            'features' => ['Transferencia ilimitada', 'IVA incluido'],
        ],
    ],
    
    // Cobertura
    'cobertura' => [
        'zonas' => ['Panguipulli', 'Choshuenco', 'Neltume', 'Coñaripe', 'Lago Ranco'],
    ],
    
    // Instalación
    'instalacion' => [
        'costo_base' => '$20.000',
        'nota' => 'Valor final sujeto a factibilidad técnica',
    ],
    
    // Configuración de OpenAI (opcional)
    'openai' => [
        'enabled' => false, // Cambiar a true para usar IA avanzada
        'api_key' => '', // Tu API key de OpenAI
        'model' => 'gpt-3.5-turbo',
    ],
    
    // Configuración de WhatsApp Business API (opcional)
    'whatsapp_api' => [
        'enabled' => false, // Cambiar a true para usar WhatsApp Business API
        'phone_number_id' => '',
        'business_account_id' => '',
        'access_token' => '',
        'webhook_verify_token' => 'connectchile_webhook_token_2024',
    ],
    
    // Mensajes del chatbot
    'messages' => [
        'welcome' => '¡Hola! 👋 Soy el asistente virtual de Connect Chile. ¿En qué puedo ayudarte?',
        'fallback' => 'Entiendo. Para ayudarte mejor, elige una opción del menú o escribe tu consulta.',
        'goodbye' => '¡Hasta luego! 👋 Que tengas un excelente día.',
    ],
];

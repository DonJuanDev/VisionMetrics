<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API Configuration
    |--------------------------------------------------------------------------
    */

    'cloud_api' => [
        'token' => env('WHATSAPP_CLOUD_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'base_url' => 'https://graph.facebook.com/v18.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Processing Configuration
    |--------------------------------------------------------------------------
    */

    'message_processing' => [
        'auto_nlp' => env('WHATSAPP_AUTO_NLP', true),
        'nlp_confidence_threshold' => env('WHATSAPP_NLP_THRESHOLD', 0.8),
        'max_message_length' => 4096,
        'supported_attachments' => [
            'image' => ['jpg', 'jpeg', 'png', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'audio' => ['mp3', 'ogg', 'wav', 'aac'],
            'video' => ['mp4', 'webm'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Automation Rules
    |--------------------------------------------------------------------------
    */

    'automation' => [
        'follow_up_delay_hours' => env('WHATSAPP_FOLLOWUP_DELAY', 24),
        'max_automated_messages' => env('WHATSAPP_MAX_AUTO_MSG', 3),
        'business_hours' => [
            'start' => '08:00',
            'end' => '18:00',
            'timezone' => 'America/Sao_Paulo',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion Detection Patterns
    |--------------------------------------------------------------------------
    */

    'conversion_patterns' => [
        'keywords' => [
            'fechado', 'pago', 'comprou', 'vendido', 'confirmado', 
            'boleto', 'pix', 'cartão', 'transferência', 'aprovado'
        ],
        'value_patterns' => [
            '/R\$\s*(\d+(?:\.\d{3})*(?:,\d{2})?)/i',
            '/(\d+(?:\.\d{3})*(?:,\d{2})?)[\s]*reais?/i',
            '/valor[:\s]*R\$\s*(\d+(?:\.\d{3})*(?:,\d{2})?)/i',
            '/preço[:\s]*R\$\s*(\d+(?:\.\d{3})*(?:,\d{2})?)/i',
        ],
        'payment_methods' => [
            'pix' => ['pix', 'chave pix', 'qr code'],
            'boleto' => ['boleto', 'código de barras'],
            'cartao' => ['cartão', 'cartao', 'débito', 'crédito'],
            'transferencia' => ['transferência', 'ted', 'doc'],
        ],
    ],

];

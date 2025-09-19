<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Message;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['whatsapp.cloud_api.webhook_verify_token' => 'test_verify_token']);
    }

    public function test_webhook_verification()
    {
        $response = $this->getJson('/api/webhooks/whatsapp?' . http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'test_verify_token',
            'hub.challenge' => 'test_challenge',
        ]));

        $response->assertStatus(200);
        $this->assertEquals('test_challenge', $response->getContent());
    }

    public function test_webhook_verification_fails_with_wrong_token()
    {
        $response = $this->getJson('/api/webhooks/whatsapp?' . http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'wrong_token',
            'hub.challenge' => 'test_challenge',
        ]));

        $response->assertStatus(403);
    }

    public function test_webhook_message_processing()
    {
        $company = Company::factory()->create();
        
        $webhookPayload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messages' => [
                                    [
                                        'id' => 'whatsapp_message_123',
                                        'from' => '5511999999999',
                                        'timestamp' => now()->timestamp,
                                        'text' => [
                                            'body' => 'Olá, gostaria de mais informações'
                                        ],
                                        'type' => 'text'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhooks/whatsapp', $webhookPayload);

        $response->assertStatus(200);

        // Verificar se lead foi criado
        $this->assertDatabaseHas('leads', [
            'phone' => '5511999999999',
        ]);

        // Verificar se conversa foi criada
        $lead = Lead::where('phone', '5511999999999')->first();
        $this->assertDatabaseHas('conversations', [
            'lead_id' => $lead->id,
            'status' => 'open',
        ]);

        // Verificar se mensagem foi criada
        $conversation = Conversation::where('lead_id', $lead->id)->first();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'whatsapp_message_id' => 'whatsapp_message_123',
            'sender' => 'client',
            'body' => 'Olá, gostaria de mais informações',
        ]);
    }

    public function test_webhook_message_with_value_detection()
    {
        $company = Company::factory()->create();
        
        $webhookPayload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messages' => [
                                    [
                                        'id' => 'whatsapp_message_456',
                                        'from' => '5511888888888',
                                        'timestamp' => now()->timestamp,
                                        'text' => [
                                            'body' => 'Fechado! Paguei R$ 1.500,00 no PIX'
                                        ],
                                        'type' => 'text'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhooks/whatsapp', $webhookPayload);

        $response->assertStatus(200);

        // Verificar se mensagem foi processada e valor detectado
        $message = Message::where('whatsapp_message_id', 'whatsapp_message_456')->first();
        $this->assertNotNull($message);
        
        // Como o processamento é assíncrono, simular o processamento
        $message->parseForValues();
        $message->refresh();
        
        $this->assertTrue($message->is_parsed);
        $this->assertEquals(1500.00, $message->parsed_value);
    }

    public function test_webhook_ignores_invalid_payload()
    {
        $response = $this->postJson('/api/webhooks/whatsapp', [
            'invalid' => 'payload'
        ]);

        $response->assertStatus(200);
        
        // Não deve criar nenhum registro
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('messages', 0);
    }
}

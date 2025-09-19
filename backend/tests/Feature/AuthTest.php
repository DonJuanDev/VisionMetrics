<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_registration()
    {
        $response = $this->postJson('/api/register-company', [
            'company_name' => 'Empresa Teste',
            'admin_name' => 'Admin Teste',
            'admin_email' => 'admin@teste.com',
            'phone' => '11999999999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'company' => ['id', 'name', 'email'],
                'user' => ['id', 'name', 'email'],
                'token',
                'trial_info'
            ]);

        // Verificar se empresa foi criada
        $this->assertDatabaseHas('companies', [
            'name' => 'Empresa Teste',
            'email' => 'admin@teste.com',
        ]);

        // Verificar se usuário admin foi criado
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'role' => 'company_admin',
        ]);

        // Verificar se trial foi configurado
        $company = Company::first();
        $this->assertNotNull($company->trial_expires_at);
        $this->assertTrue($company->trial_expires_at->isFuture());
    }

    public function test_user_login()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'company' => ['id', 'name']
            ]);
    }

    public function test_login_with_expired_trial()
    {
        $company = Company::factory()->create([
            'trial_expires_at' => now()->subDay(),
        ]);
        
        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Login deve funcionar
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        // Mas acessar endpoints protegidos deve retornar trial expired
        $token = $response->json('token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/dashboard');

        $response->assertStatus(402)
            ->assertJson(['error' => 'trial_expired']);
    }

    public function test_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);

        // Token deve ser revogado
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_me_endpoint()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'company' => ['id', 'name'],
                'permissions'
            ]);
    }
}

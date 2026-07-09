<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('Criar conta');
    }

    public function test_new_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Aluno Teste',
            'email' => 'aluno@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('polls.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'aluno@example.com']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'aluno@example.com']);

        $response = $this->post('/register', [
            'name' => 'Outro',
            'email' => 'aluno@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_must_be_confirmed(): void
    {
        $response = $this->post('/register', [
            'name' => 'Aluno',
            'email' => 'novo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}

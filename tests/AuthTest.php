<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase {
    use DatabaseMigrations;

    // Common function to create a user
    private function createUser($email = 'johndoe@example.com', $password = 'password123') {
        return User::create([
            'name' => 'John Doe',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    /** @test */
    public function it_registers_a_user_successfully() {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->seeStatusCode(201)
            ->seeJson(['message' => 'User registered successfully']);

        $this->seeInDatabase('users', ['email' => 'johndoe@example.com']);
    }

    /** @test */
    public function it_fails_registration_with_missing_required_fields() {
        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->seeStatusCode(422);
        $response->seeJson([
            'name' => ['The name field is required.']
        ]);
    }

    /** @test */
    public function it_fails_registration_with_invalid_email_format() {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email-format',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['email' => ['The email must be a valid email address.']]);
    }

    /** @test */
    public function it_fails_registration_with_existing_email() {
        $this->createUser(); // Create a user with the same email

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->seeStatusCode(422);
    }

    /** @test */
    public function it_logs_in_a_user_successfully() {
        $user = $this->createUser(); // Use the common function to create a user

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->seeStatusCode(200)
            ->seeJsonStructure(['access_token', 'token_type', 'user', 'expires_in']);
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials() {
        $this->createUser(); // Create a user

        $response = $this->post('/login', [
            'email' => 'johndoe@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->seeStatusCode(401)
            ->seeJson(['message' => 'Unauthorized']);
    }

    /** @test */
    public function it_fetches_authenticated_user_details() {
        $user = $this->createUser(); // Use the common function to create a user

        $token = Auth::attempt(['email' => $user->email, 'password' => 'password123']);

        $this->assertNotNull($token, 'Token should not be null');

        $response = $this->get('/me', ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(200)
            ->seeJson(['email' => $user->email]);
    }

    /** @test */
    public function it_logs_out_a_user() {
        $user = $this->createUser(); // Create a user

        $token = Auth::attempt(['email' => $user->email, 'password' => 'password123']);

        $this->assertNotNull($token, 'Token should not be null');

        $response = $this->post('/logout', [], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(200)
            ->seeJson(['message' => 'Successfully logged out']);
    }

    /** @test */
    public function it_refreshes_a_token() {
        $user = $this->createUser(); // Create a user

        $token = Auth::attempt(['email' => $user->email, 'password' => 'password123']);

        $response = $this->post('/refresh', [], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(200);

        $response->seeJson([
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'user' => [
                'email' => $user->email,
                'id' => $user->id,
                'name' => $user->name,
            ]
        ]);
        $response->seeJsonStructure([
            'access_token'
        ]);
    }
}

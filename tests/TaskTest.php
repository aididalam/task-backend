<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class TaskTest extends TestCase {
    use DatabaseMigrations;

    // Helper function to create a user and get an authentication token
    private function createUserAndGetToken() {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123')
        ]);

        $token = Auth::attempt(['email' => $user->email, 'password' => 'password123']);
        return ['user' => $user, 'token' => $token];
    }

    /** @test */
    public function it_creates_a_task_successfully() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];

        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-03-15',
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(201)
            ->seeJson(['name' => 'Test Task']);

        // Verify task is created in the database
        $this->assertTrue(DB::table('tasks')->where('name', 'Test Task')->exists());
    }

    /** @test */
    public function it_requires_authentication_to_create_a_task() {
        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-03-15',
        ]);

        $response->seeStatusCode(401); // Unauthorized status
    }

    /** @test */
    public function it_validates_required_fields_when_creating_a_task() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];

        $response = $this->post('/tasks/', [
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-03-15',
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(422); // Validation error
        $response->seeJsonStructure(['name']);
    }

    /** @test */
    public function it_validates_due_date_format_when_creating_a_task() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];

        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-15-03', // Invalid date format
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(422); // Validation error
        $response->seeJsonStructure(['due_date']);
    }

    /** @test */
    public function it_ignores_extra_fields_when_creating_a_task() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];

        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-03-15',
            'extra_field' => 'Should not be saved',
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(201); // Task created successfully
        $task = DB::table('tasks')->where('name', 'Test Task')->first();
        $this->assertObjectNotHasProperty('extra_field', $task);
    }

    /** @test */
    public function it_validates_invalid_status_when_creating_a_task() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];

        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'Invalid Status', // Invalid status
            'due_date' => '2025-03-15',
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(422); // Validation error
        $response->seeJsonStructure(['status']);
    }

    /** @test */
    public function it_creates_a_task_for_the_authenticated_user() {
        $userData = $this->createUserAndGetToken();
        $token = $userData['token'];
        $user = $userData['user'];

        $response = $this->post('/tasks/', [
            'name' => 'Test Task',
            'description' => 'Test task description',
            'status' => 'To Do',
            'due_date' => '2025-03-15',
        ], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(201); // Task created successfully

        $task = DB::table('tasks')->where('name', 'Test Task')->first();

        // Check the user_id in the task
        $this->assertNotNull($task);
        $this->assertEquals($user->id, $task->user_id); // Assuming user_id is the foreign key
    }
}

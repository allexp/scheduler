<?php
namespace Tests\Feature;
use App\Jobs\SendAppointmentNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
class BookingApiTest extends TestCase {
    use RefreshDatabase;
    public function test_user_can_register():void{$this->postJson('/api/register',['name'=>'Анна','email'=>'anna@example.com','password'=>'password','password_confirmation'=>'password'])->assertCreated()->assertJsonPath('user.role','employee')->assertJsonStructure(['token']);}
    public function test_user_can_login_with_remember_option():void{
        User::factory()->create(['email'=>'anna@example.com','password'=>'password']);
        $this->postJson('/api/login',['email'=>'anna@example.com','password'=>'password','remember'=>true])->assertOk()->assertJsonStructure(['user','token']);
    }
    public function test_employee_can_create_client_and_appointment():void{
        Queue::fake();$token='employee-token';$employee=User::factory()->create(['role'=>'employee','api_token'=>hash('sha256',$token)]);
        $client=$this->withToken($token)->postJson('/api/clients',['first_name'=>'Иван','last_name'=>'Петров','phone'=>'+79990000000'])->assertCreated()->json();
        $this->withToken($token)->postJson('/api/appointments',['client_id'=>$client['id'],'employee_id'=>$employee->id,'service'=>'Консультация','starts_at'=>'2030-01-10 10:00:00','ends_at'=>'2030-01-10 11:00:00'])->assertCreated();
        Queue::assertPushed(SendAppointmentNotification::class);
    }

    public function test_employee_can_update_client(): void
    {
        $token = 'employee-token';
        User::factory()->create(['role' => 'employee', 'api_token' => hash('sha256', $token)]);
        $client = $this->withToken($token)->postJson('/api/clients', [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
        ])->assertCreated()->json();

        $this->withToken($token)->patchJson('/api/clients/'.$client['id'], [
            'first_name' => 'Иван',
            'last_name' => 'Сидоров',
            'phone' => '+79991112233',
            'email' => 'ivan@example.com',
            'birthday' => '1990-05-15',
            'notes' => 'Предпочитает утренние записи',
        ])->assertOk()
            ->assertJsonPath('full_name', 'Сидоров Иван')
            ->assertJsonPath('phone', '+79991112233');

        $this->assertDatabaseHas('clients', [
            'id' => $client['id'],
            'last_name' => 'Сидоров',
            'email' => 'ivan@example.com',
        ]);
    }
    public function test_employee_cannot_open_history():void{$token='test-token';User::factory()->create(['role'=>'employee','api_token'=>hash('sha256',$token)]);$this->withToken($token)->getJson('/api/history')->assertForbidden();}

    public function test_only_admin_can_manage_users(): void
    {
        $employeeToken = 'employee-token';
        User::factory()->create(['role' => 'employee', 'api_token' => hash('sha256', $employeeToken)]);
        $this->withToken($employeeToken)->getJson('/api/users')->assertForbidden();

        $adminToken = 'admin-token';
        User::factory()->create(['role' => 'admin', 'api_token' => hash('sha256', $adminToken)]);
        $created = $this->withToken($adminToken)->postJson('/api/users', [
            'name' => 'Новый сотрудник',
            'email' => 'new@example.com',
            'role' => 'employee',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated()->assertJsonPath('role', 'employee')->json();

        $this->withToken($adminToken)->putJson('/api/users/'.$created['id'], [
            'name' => 'Новый администратор',
            'email' => 'new@example.com',
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])->assertOk()->assertJsonPath('role', 'admin');

        $this->withToken($adminToken)->deleteJson('/api/users/'.$created['id'])->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $created['id']]);
    }

    public function test_admin_cannot_delete_self_or_demote_last_admin(): void
    {
        $token = 'admin-token';
        $admin = User::factory()->create(['role' => 'admin', 'api_token' => hash('sha256', $token)]);

        $this->withToken($token)->deleteJson('/api/users/'.$admin->id)->assertUnprocessable();
        $this->withToken($token)->putJson('/api/users/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'employee',
            'password' => '',
            'password_confirmation' => '',
        ])->assertUnprocessable();
    }
}

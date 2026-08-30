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
    public function test_employee_cannot_open_history():void{$token='test-token';User::factory()->create(['role'=>'employee','api_token'=>hash('sha256',$token)]);$this->withToken($token)->getJson('/api/history')->assertForbidden();}
}

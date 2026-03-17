<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->agent = User::factory()->create(['role' => 'agent']);
    }

    /**
     * Test - Admin peut voir la page de pointage
     */
    public function test_admin_can_view_attendance_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pointage');
        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.index');
    }

    /**
     * Test - Admin peut voir les détails de pointage d'un agent
     */
    public function test_admin_can_view_agent_attendance(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/pointage/{$this->agent->id}");
        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.show');
    }

    /**
     * Test - Admin peut marquer la présence d'un agent
     */
    public function test_admin_can_mark_agent_present(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/pointage/{$this->agent->id}/presence", [
                'date' => now()->format('Y-m-d'),
                'statut' => 'present',
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'agent_id' => $this->agent->id,
            'statut' => 'present',
        ]);
    }

    /**
     * Test - Admin peut enregistrer un check-in
     */
    public function test_admin_can_check_in(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/pointage/{$this->agent->id}/checkin");
        
        $response->assertRedirect();
        
        $attendance = Attendance::where('agent_id', $this->agent->id)
            ->whereNotNull('check_in')
            ->first();
        
        $this->assertNotNull($attendance);
    }

    /**
     * Test - Admin peut enregistrer un check-out
     */
    public function test_admin_can_check_out(): void
    {
        // D'abord créer une présence avec check-in
        Attendance::create([
            'agent_id' => $this->agent->id,
            'date' => now()->format('Y-m-d'),
            'check_in' => now()->format('H:i:s'),
            'statut' => 'present',
        ]);
        
        $response = $this->actingAs($this->admin)
            ->post("/admin/pointage/{$this->agent->id}/checkout");
        
        $response->assertRedirect();
    }

    /**
     * Test - Admin peut justifier une absence
     */
    public function test_admin_can_justify_absence(): void
    {
        $attendance = Attendance::create([
            'agent_id' => $this->agent->id,
            'date' => now()->format('Y-m-d'),
            'statut' => 'absent',
        ]);
        
        $response = $this->actingAs($this->admin)
            ->post("/admin/pointage/{$attendance->id}/justifier", [
                'motif' => 'Congé maladie',
            ]);
        
        $response->assertRedirect();
        $attendance->refresh();
        $this->assertTrue($attendance->justifiee);
    }

    /**
     * Test - Admin peut voir le rapport de pointage
     */
    public function test_admin_can_view_attendance_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pointage/rapport');
        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.rapport');
    }

    /**
     * Test - Citoyen ne peut pas accéder au pointage
     */
    public function test_citoyen_cannot_access_attendance(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($citoyen)->get('/admin/pointage');
        $response->assertStatus(403);
    }
}

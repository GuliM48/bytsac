<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Plan $plan;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar el seeder de roles y permisos para cada entorno de prueba
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Crear infraestructura base reutilizable para el Tenant 1
        $this->admin = User::factory()->create(['tenant_id' => 1]);
        $this->admin->assignRole('administrador');

        $this->plan = Plan::factory()->create([
            'tenant_id' => 1,
            'activo' => true
        ]);

        $this->client = Client::factory()->create([
            'tenant_id' => 1,
            'estado' => 'activo',
            'id_usuario_creador' => $this->admin->id
        ]);
    }

    public function test_un_administrador_puede_crear_una_suscripcion(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/subscriptions', [
                'client_id' => $this->client->id,
                'plan_id' => $this->plan->id,
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activo',
                'renovacion_automatica' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['estado' => 'activo']);
            
        $this->assertDatabaseHas('subscriptions', [
            'client_id' => $this->client->id,
            'plan_id' => $this->plan->id,
            'tenant_id' => 1
        ]);
    }

    public function test_un_cliente_no_puede_crear_suscripciones(): void
    {
        $clienteUsuario = User::factory()->create(['tenant_id' => 1]);
        $clienteUsuario->assignRole('cliente');

        $response = $this->actingAs($clienteUsuario, 'sanctum')
            ->postJson('/api/subscriptions', [
                'client_id' => $this->client->id,
                'plan_id' => $this->plan->id,
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activo',
                'renovacion_automatica' => true,
            ]);

        $response->assertStatus(403);
    }

    public function test_no_se_puede_crear_suscripcion_para_un_cliente_de_otro_tenant(): void
    {
        // Cliente que pertenece a otra organización (Tenant 99)
        $otroClient = Client::factory()->create([
            'tenant_id' => 99,
            'estado' => 'activo'
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/subscriptions', [
                'client_id' => $otroClient->id,
                'plan_id' => $this->plan->id,
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activo',
                'renovacion_automatica' => true,
            ]);

        // Debe fallar la validación porque el cliente no pertenece al Tenant 1 del Admin
        $response->assertStatus(422)
            ->assertJsonValidationErrors('client_id');
    }

    public function test_puede_listar_suscripciones_filtradas_por_su_propio_tenant(): void
    {
        // Suscripción legítima Tenant 1
        Subscription::factory()->create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'plan_id' => $this->plan->id,
            'estado' => 'activo',
        ]);

        // Suscripción intrusa de otro Tenant (No debería aparecer en el listado)
        Subscription::factory()->create([
            'tenant_id' => 2,
            'client_id' => Client::factory()->create(['tenant_id' => 2])->id,
            'plan_id' => Plan::factory()->create(['tenant_id' => 2])->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/subscriptions?estado=activo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Solo devuelve la del Tenant 1 gracias al Scope global
            ->assertJsonFragment(['estado' => 'activo']);
    }

    public function test_puede_alternar_la_renovacion_automatica_toggle(): void
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'plan_id' => $this->plan->id,
            'renovacion_automatica' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/subscriptions/{$subscription->id}/toggle-auto-renew");

        $response->assertStatus(200)
            ->assertJsonFragment(['renovacion_automatica' => true]);
    }

    public function test_puede_renovar_una_suscripcion_vencida(): void
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'plan_id' => $this->plan->id,
            'fecha_fin' => now()->subDays(5),
            'estado' => 'vencido',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/subscriptions/{$subscription->id}/renew");

        $response->assertStatus(200)
            ->assertJsonFragment(['estado' => 'activo']);

        $subscription->refresh();
        $this->assertEquals('activo', $subscription->estado);
    }

    /**
     * NUEVO: Test de eliminación exitosa (Happy Path)
     */
    public function test_un_administrador_puede_eliminar_una_suscripcion(): void
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'plan_id' => $this->plan->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/subscriptions/{$subscription->id}");

        // El controlador retorna un 204 No Content si se borra con éxito
        $response->assertStatus(204);
        
        // Verificamos que ya no exista físicamente en la BD
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }

    /**
     * NUEVO: Test de Seguridad Crítica Multi-Tenant para eliminación
     */
    public function test_no_se_puede_eliminar_una_suscripcion_de_otro_tenant(): void
    {
        // Creamos una suscripción que pertenece estrictamente al Tenant 2
        $subscriptionAjena = Subscription::factory()->create([
            'tenant_id' => 2,
            'client_id' => Client::factory()->create(['tenant_id' => 2])->id,
            'plan_id' => Plan::factory()->create(['tenant_id' => 2])->id,
        ]);

        // Intentamos borrarla con el Admin del Tenant 1
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/subscriptions/{$subscriptionAjena->id}");

        // Debe retornar un 403 Forbidden o un 404 debido al aislamiento de Tenancy
        $response->assertStatus(403);
        
        // El registro debe permanecer intacto en la base de datos
        $this->assertDatabaseHas('subscriptions', ['id' => $subscriptionAjena->id]);
    }
}
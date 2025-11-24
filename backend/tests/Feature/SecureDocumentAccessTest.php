<?php

namespace Tests\Feature;

use App\Models\AccessAuditLog;
use App\Models\Document;
use App\Models\DocumentAccessToken;
use App\Models\User;
use App\Services\DocumentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecureDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $approver;
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->approver = User::factory()->create(['role' => 'user']);
    }

    #[Test]
    public function authorized_user_can_generate_access_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens", [
                'expires_in_hours' => 48,
                'purpose' => 'Share with external reviewer',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token_id',
                'access_url',
                'expires_at',
                'expires_in_hours',
            ]);

        $this->assertDatabaseHas('document_access_tokens', [
            'document_id' => $document->id,
            'generated_by' => $this->user->id,
        ]);
    }

    #[Test]
    public function unauthorized_user_cannot_generate_access_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
        ]);

        $otherUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens");

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_generate_access_token_for_any_document()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens");

        $response->assertStatus(201);
    }

    #[Test]
    public function approver_can_generate_access_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [[$this->approver->id]],
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->approver, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens");

        $response->assertStatus(201);
    }

    #[Test]
    public function valid_token_allows_document_access()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
        ]);

        $accessService = app(DocumentAccessService::class);
        $token = $accessService->generateToken($document, $this->user, 24);

        $response = $this->getJson("/api/secure/documents/{$token->token}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'document' => ['id', 'title', 'status'],
                'approval_progress',
                'token_expires_at',
            ]);

        // Verify access was logged
        $this->assertDatabaseHas('access_audit_logs', [
            'document_id' => $document->id,
            'access_token_id' => $token->id,
            'action' => 'view',
            'success' => true,
        ]);
    }

    #[Test]
    public function invalid_token_denies_document_access()
    {
        $response = $this->getJson("/api/secure/documents/invalid-token-here");

        $response->assertStatus(403);

        // Verify failed access was logged
        $this->assertDatabaseHas('access_audit_logs', [
            'action' => 'access_attempt',
            'success' => false,
            'failure_reason' => 'Invalid or expired token',
        ]);
    }

    #[Test]
    public function expired_token_denies_document_access()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $token = DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->subHour(), // Expired 1 hour ago
        ]);

        $response = $this->getJson("/api/secure/documents/{$token->token}");

        $response->assertStatus(403);
    }

    #[Test]
    public function revoked_token_denies_document_access()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $token = DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        $token->revoke('Security concern');

        $response = $this->getJson("/api/secure/documents/{$token->token}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_revoke_their_own_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $token = DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens/{$token->id}/revoke", [
                'reason' => 'No longer needed',
            ]);

        $response->assertStatus(200);

        $token->refresh();
        $this->assertNotNull($token->revoked_at);
        $this->assertEquals('No longer needed', $token->revoked_reason);
    }

    #[Test]
    public function unauthorized_user_cannot_revoke_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $token = DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        $otherUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens/{$token->id}/revoke");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_rotate_token()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $oldToken = DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/documents/{$document->id}/access-tokens/{$oldToken->id}/rotate", [
                'expires_in_hours' => 48,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token_id',
                'access_url',
                'expires_at',
                'old_token_id',
                'message',
            ]);

        $oldToken->refresh();
        $this->assertNotNull($oldToken->revoked_at);
        $this->assertEquals('Rotated to new token', $oldToken->revoked_reason);

        // Verify new token exists
        $newTokenId = $response->json('token_id');
        $this->assertDatabaseHas('document_access_tokens', [
            'id' => $newTokenId,
            'document_id' => $document->id,
        ]);
    }

    #[Test]
    public function user_can_list_active_tokens()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        // Create multiple tokens
        DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->addWeek(),
        ]);

        // Create an expired token (should not be in list)
        DocumentAccessToken::create([
            'document_id' => $document->id,
            'token' => DocumentAccessToken::generateSecureToken(),
            'generated_by' => $this->user->id,
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/documents/{$document->id}/access-tokens");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tokens' => [
                    '*' => [
                        'id',
                        'generated_by',
                        'expires_at',
                        'access_count',
                        'last_accessed_at',
                        'metadata',
                        'created_at',
                    ],
                ],
            ]);

        // Should have 2 active tokens
        $this->assertCount(2, $response->json('tokens'));
    }

    #[Test]
    public function access_logs_track_document_access()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $accessService = app(DocumentAccessService::class);
        $token = $accessService->generateToken($document, $this->user, 24);

        // Access document multiple times
        $this->getJson("/api/secure/documents/{$token->token}");
        $this->getJson("/api/secure/documents/{$token->token}");

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/documents/{$document->id}/access-logs?hours=24");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'total_accesses',
                    'successful_accesses',
                    'failed_accesses',
                    'unique_users',
                    'unique_ips',
                    'actions',
                ],
                'logs' => [
                    'data' => [],
                ],
            ]);

        $stats = $response->json('stats');
        $this->assertEquals(2, $stats['total_accesses']);
        $this->assertEquals(2, $stats['successful_accesses']);
    }

    #[Test]
    public function unauthorized_user_cannot_view_access_logs()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $otherUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/documents/{$document->id}/access-logs");

        $response->assertStatus(403);
    }

    #[Test]
    public function token_access_count_increments_on_use()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $accessService = app(DocumentAccessService::class);
        $token = $accessService->generateToken($document, $this->user, 24);

        $this->assertEquals(0, $token->access_count);

        // Access document
        $this->getJson("/api/secure/documents/{$token->token}");

        $token->refresh();
        $this->assertEquals(1, $token->access_count);
        $this->assertNotNull($token->last_accessed_at);
    }

    #[Test]
    public function document_creation_automatically_generates_secure_token()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $approver = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', [
                'title' => 'Test Document',
                'description' => 'Test Description',
                'file' => $file,
                'approvers' => [[$approver->id]],
                'qr_x' => 0.8,
                'qr_y' => 0.9,
                'qr_page' => 1,
            ]);

        $response->assertStatus(201);

        $document = Document::find($response->json('id'));

        // Verify token was created
        $this->assertDatabaseHas('document_access_tokens', [
            'document_id' => $document->id,
            'generated_by' => $this->user->id,
        ]);

        // Verify token has QR code metadata
        $token = DocumentAccessToken::where('document_id', $document->id)->first();
        $this->assertArrayHasKey('purpose', $token->metadata);
        $this->assertEquals('QR Code access', $token->metadata['purpose']);
    }
}

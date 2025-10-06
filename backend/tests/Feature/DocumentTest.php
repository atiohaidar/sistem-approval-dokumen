<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
    }

    #[Test]
    public function user_can_list_documents()
    {
        // Create some test documents
        Document::factory()->count(3)->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'file_path',
                        'file_name',
                        'status',
                        'created_by',
                        'created_at',
                        'creator' => [
                            'id',
                            'name',
                            'email'
                        ]
                    ]
                ],
                'current_page',
                'per_page',
                'total'
            ]);
    }

    #[Test]
    public function user_can_create_document()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $data = [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'file' => $file,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
                'status',
                'created_by',
                'creator' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'created_by' => $this->user->id,
            'status' => 'draft'
        ]);

        Storage::disk('public')->assertExists('documents/' . basename($response->json('file_path')));
    }

    #[Test]
    public function user_can_view_document()
    {
        $document = Document::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'status' => $document->status,
            ]);
    }

    #[Test]
    public function user_can_update_own_draft_document()
    {
        Storage::fake('public');

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'draft'
        ]);

        $newFile = UploadedFile::fake()->create('updated.pdf', 2000);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'file' => $newFile,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/documents/{$document->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Title',
                'description' => 'Updated description',
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    #[Test]
    public function user_cannot_update_non_draft_document()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/documents/{$document->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot update document that is not in draft status'
            ]);
    }

    #[Test]
    public function user_can_delete_own_draft_document()
    {
        Storage::fake('public');

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Document deleted successfully'
            ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id
        ]);
    }

    #[Test]
    public function user_cannot_delete_non_draft_document()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/documents/{$document->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot delete document that is not in draft status'
            ]);
    }

    #[Test]
    public function user_cannot_update_other_users_document()
    {
        $otherUser = User::factory()->create();
        $document = Document::factory()->create([
            'created_by' => $otherUser->id,
            'status' => 'draft'
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/documents/{$document->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized'
            ]);
    }

    #[Test]
    public function user_cannot_delete_other_users_document()
    {
        $otherUser = User::factory()->create();
        $document = Document::factory()->create([
            'created_by' => $otherUser->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/documents/{$document->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized'
            ]);
    }

    #[Test]
    public function document_creation_requires_valid_file()
    {
        $data = [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'file' => 'not-a-file', // Invalid file
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(422);
    }

    #[Test]
    public function document_creation_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.txt', 1000); // Invalid file type

        $data = [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'file' => $file,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(422);
    }
}

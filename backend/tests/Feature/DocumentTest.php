<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\QRCodeService;
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

        // Create additional users for approvers
        $approver1 = User::factory()->create();
        $approver2 = User::factory()->create();

        $data = [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'file' => $file,
            'approvers' => [$approver1->id, $approver2->id],
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
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
                'approvers',
                'qr_x',
                'qr_y',
                'qr_page',
                'total_steps',
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
            'status' => 'pending_approval',
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
            'total_steps' => 2,
        ]);

        // Verify approvers are stored correctly
        $document = Document::find($response->json('id'));
        $this->assertEquals([$approver1->id, $approver2->id], $document->approvers);

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
            'status' => 'pending_approval'
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
            'status' => 'pending_approval'
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

    #[Test]
    public function user_can_download_document_with_watermark_when_not_approved()
    {
        // Create a dummy PDF file in storage
        $dummyPdfPath = 'documents/test-document.pdf';
        $dummyPdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 5 0 R
>>
>>
>>
endobj
4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
100 700 Td
(Hello World) Tj
ET
endstream
endobj
5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000274 00000 n
0000000373 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
459
%%EOF';

        Storage::disk('public')->put($dummyPdfPath, $dummyPdfContent);

        // Create a document that is not approved
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
            'approvers' => json_encode([$this->user->id + 1, $this->user->id + 2]),
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
            'file_path' => $dummyPdfPath,
            'file_name' => 'test-document.pdf'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/documents/{$document->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=test-document.pdf');

        // Clean up
        Storage::disk('public')->delete($dummyPdfPath);
    }

    #[Test]
    public function user_can_download_document_without_watermark_when_approved()
    {
        // Create a dummy PDF file in storage
        $dummyPdfPath = 'documents/test-approved-document.pdf';
        $dummyPdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 5 0 R
>>
>>
>>
endobj
4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
100 700 Td
(Hello World) Tj
ET
endstream
endobj
5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000274 00000 n
0000000373 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
459
%%EOF';

        Storage::disk('public')->put($dummyPdfPath, $dummyPdfContent);

        // Create a document that is approved
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'completed',
            'approvers' => json_encode([$this->user->id + 1]),
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
            'file_path' => $dummyPdfPath,
            'file_name' => 'test-approved-document.pdf'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/documents/{$document->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');

        // Clean up
        Storage::disk('public')->delete($dummyPdfPath);
    }

    #[Test]
    public function user_can_create_document_with_approvers_as_string()
    {
        $approver1 = User::factory()->create();
        $approver2 = User::factory()->create();

        $data = [
            'title' => 'Test Document with String Approvers',
            'description' => 'This is a test document with string approvers',
            'file' => UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
            'approvers' => '[' . $approver1->id . ',' . $approver2->id . ']', // String format
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'approvers',
                'qr_x',
                'qr_y',
                'qr_page',
                'status',
                'created_by',
                'created_at',
            ]);

        $document = Document::find($response->json('id'));
        $this->assertEquals([$approver1->id, $approver2->id], $document->approvers);
        $this->assertEquals('pending_approval', $document->status);
        $this->assertEquals(2, $document->total_steps);
    }

    #[Test]
    public function user_cannot_download_other_users_document()
    {
        $otherUser = User::factory()->create();
        $document = Document::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/documents/{$document->id}/download");

        $response->assertStatus(403);
    }

    #[Test]
    public function public_can_access_document_info_via_qr_code()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [$this->user->id],
            'status' => 'pending_approval',
            'current_step' => 0,
            'total_steps' => 1,
        ]);

        // Access public info without authentication
        $response = $this->getJson("/api/documents/{$document->id}/public-info");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'document' => [
                    'id', 'title', 'description', 'status', 'file_name',
                    'file_size', 'mime_type', 'created_at', 'submitted_at', 'creator'
                ],
                'approvers' => [
                    '*' => ['id', 'name', 'email', 'status', 'approved_at', 'notes']
                ],
                'progress' => [
                    'total_approvers', 'approved_count', 'pending_count',
                    'rejected_count', 'current_step', 'completion_percentage'
                ],
                'workflow' => [
                    'is_sequential', 'can_download', 'next_approver'
                ]
            ]);

        // Verify data accuracy
        $responseData = $response->json();
        $this->assertEquals($document->id, $responseData['document']['id']);
        $this->assertEquals($document->title, $responseData['document']['title']);
        $this->assertEquals(1, $responseData['progress']['total_approvers']);
        $this->assertEquals(0, $responseData['progress']['approved_count']);
        $this->assertEquals(1, $responseData['progress']['pending_count']);
    }

    #[Test]
    public function qr_code_contains_url_to_public_info()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [$this->user->id],
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ]);

        $qrService = app(QRCodeService::class);
        $qrUrl = $qrService->getQRUrl($document);
        $expectedUrl = url('/api/documents/' . $document->id . '/public-info');

        $this->assertEquals($expectedUrl, $qrUrl);
    }

    #[Test]
    public function qr_coordinates_are_correctly_stored()
    {
        // Test coordinate format
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'qr_x' => 0.5,
            'qr_y' => 0.5,
            'qr_page' => 1,
        ]);

        $this->assertEquals(0.5, $document->qr_x);
        $this->assertEquals(0.5, $document->qr_y);
        $this->assertEquals(1, $document->qr_page);

        // Test different coordinates
        $document2 = Document::factory()->create([
            'created_by' => $this->user->id,
            'qr_x' => 0.1,
            'qr_y' => 0.9,
            'qr_page' => 2,
        ]);

        $this->assertEquals(0.1, $document2->qr_x);
        $this->assertEquals(0.9, $document2->qr_y);
        $this->assertEquals(2, $document2->qr_page);
    }
}

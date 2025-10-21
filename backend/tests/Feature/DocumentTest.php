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
            'approvers' => [[$approver1->id, $approver2->id]], // Level 1 with 2 approvers
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
                'template_id',
                'status',
                'created_by',
                'submitted_at',
                'approvers',
                'current_level',
                'qr_x',
                'qr_y',
                'qr_page',
                'qr_code_path',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
            'current_level' => 1,
        ]);

        // Verify approvers are stored correctly
        $document = Document::find($response->json('id'));
        $this->assertEquals([[$approver1->id, $approver2->id]], $document->approvers);

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
            'approvers' => [[$this->user->id + 1, $this->user->id + 2]], // Level 1 with 2 approvers
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
            'approvers' => [[$this->user->id + 1]], // Level 1 with 1 approver
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
            'approvers' => '[[' . $approver1->id . ',' . $approver2->id . ']]', // String format for nested array
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
        $this->assertEquals([[$approver1->id, $approver2->id]], $document->approvers);
        $this->assertEquals('pending_approval', $document->status);
        $this->assertEquals(1, $document->current_level);
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
            'approvers' => [[$this->user->id]], // Level 1 with 1 approver
            'status' => 'pending_approval',
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
                    '*' => [
                        '*' => ['id', 'name', 'email', 'status', 'processed_at', 'notes']
                    ]
                ],
                'progress' => [
                    'total_approvers', 'approved_count', 'pending_count',
                    'rejected_count', 'current_level', 'total_levels', 'completion_percentage'
                ],
                'workflow' => [
                    'is_sequential', 'can_download', 'next_approver', 'approval_levels'
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
    public function public_info_shows_approved_at_for_completed_documents()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [[$this->user->id]], // Level 1 with 1 approver
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Initialize level progress to mark user as approved
        $document->getLevelProgress();
        $document->level_progress = ['approved' => [$this->user->id], 'pending' => []];
        $document->save();

        // Create approval record for the user
        \App\Models\DocumentApproval::create([
            'document_id' => $document->id,
            'approver_id' => $this->user->id,
            'action' => 'approved',
            'notes' => 'Approved by creator',
            'level' => 1,
            'approved_at' => now(),
        ]);

        // Access public info without authentication
        $response = $this->getJson("/api/documents/{$document->id}/public-info");

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertEquals('approved', $responseData['approvers'][0][0]['status']);
        $this->assertNotNull($responseData['approvers'][0][0]['processed_at']);
    }

    #[Test]
    public function public_info_shows_rejected_status_for_rejected_document()
    {
        $level1Approver = $this->user;
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver->id], // Level 1
                [$level2Approver->id]  // Level 2 - should be null status
            ],
            'status' => 'rejected',
            'level_progress' => ['approved' => [], 'pending' => [], 'rejected' => [$level1Approver->id]],
            'current_level' => 1,
        ]);

        // Create rejection record for level 1 approver
        \App\Models\DocumentApproval::create([
            'document_id' => $document->id,
            'approver_id' => $level1Approver->id,
            'action' => 'rejected',
            'notes' => 'Rejected',
            'level' => 1,
            'approved_at' => now(),
        ]);

        // Access public info without authentication
        $response = $this->getJson("/api/documents/{$document->id}/public-info");

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertEquals('rejected', $responseData['document']['status']);
        
        // Level 1 approver should be rejected
        $this->assertEquals('rejected', $responseData['approvers'][0][0]['status']);
        $this->assertNotNull($responseData['approvers'][0][0]['processed_at']);
        
        // Level 2 approver should be null (unprocessed) - even if same user
        $this->assertNull($responseData['approvers'][1][0]['status']);
        $this->assertNull($responseData['approvers'][1][0]['processed_at']);
        $this->assertNull($responseData['approvers'][1][0]['notes']);
    }

    #[Test]
    public function qr_code_contains_url_to_public_info()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [[$this->user->id]], // Level 1 with 1 approver
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

    #[Test]
    public function user_can_create_document_with_multiple_levels()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        // Create approvers for different levels
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();
        $level3Approver = User::factory()->create();

        $data = [
            'title' => 'Multi-Level Document',
            'description' => 'Document with multiple approval levels',
            'file' => $file,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1: 2 parallel approvers
                [$level2Approver->id], // Level 2: 1 sequential approver
                [$level3Approver->id] // Level 3: 1 sequential approver
            ],
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(201);

        $document = Document::find($response->json('id'));
        $this->assertEquals([
            [$level1Approver1->id, $level1Approver2->id],
            [$level2Approver->id],
            [$level3Approver->id]
        ], $document->approvers);
        $this->assertEquals(1, $document->current_level);
        $this->assertEquals(3, $document->getTotalLevels());
    }

    #[Test]
    public function document_creation_validates_maximum_10_levels()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        // Create 11 levels (should fail)
        $approvers = [];
        for ($i = 0; $i < 11; $i++) {
            $approvers[] = [User::factory()->create()->id];
        }

        $data = [
            'title' => 'Too Many Levels Document',
            'description' => 'Document with too many levels',
            'file' => $file,
            'approvers' => $approvers,
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['approvers']);
    }

    #[Test]
    public function document_creation_allows_exactly_10_levels()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        // Create exactly 10 levels (should pass)
        $approvers = [];
        for ($i = 0; $i < 10; $i++) {
            $approvers[] = [User::factory()->create()->id];
        }

        $data = [
            'title' => 'Maximum Levels Document',
            'description' => 'Document with maximum allowed levels',
            'file' => $file,
            'approvers' => $approvers,
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(201);

        $document = Document::find($response->json('id'));
        $this->assertEquals(10, $document->getTotalLevels());
    }

    #[Test]
    public function document_creation_validates_level_has_at_least_one_approver()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $data = [
            'title' => 'Invalid Level Document',
            'description' => 'Document with empty level',
            'file' => $file,
            'approvers' => [
                [User::factory()->create()->id], // Valid level
                [] // Empty level - should fail
            ],
            'qr_x' => 0.8,
            'qr_y' => 0.1,
            'qr_page' => 1,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['approvers.1']);
    }

    #[Test]
    public function document_get_current_approver_ids_returns_correct_users()
    {
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
        ]);

        // Should return level 1 approvers
        $currentApprovers = $document->getCurrentApproverIds();
        $this->assertEquals([$level1Approver1->id, $level1Approver2->id], $currentApprovers);

        // Move to level 2
        $document->current_level = 2;
        $document->save();

        // Should return level 2 approvers
        $currentApprovers = $document->getCurrentApproverIds();
        $this->assertEquals([$level2Approver->id], $currentApprovers);
    }

    #[Test]
    public function document_can_be_approved_by_returns_correct_result()
    {
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Level 1 approvers should be able to approve
        $this->assertTrue($document->canBeApprovedBy($level1Approver1->id));
        $this->assertTrue($document->canBeApprovedBy($level1Approver2->id));

        // Level 2 approver should not be able to approve yet
        $this->assertFalse($document->canBeApprovedBy($level2Approver->id));

        // Other user should not be able to approve
        $this->assertFalse($document->canBeApprovedBy($otherUser->id));

        // Creator should not be able to approve
        $this->assertFalse($document->canBeApprovedBy($this->user->id));
    }

    #[Test]
    public function document_level_completion_works_correctly()
    {
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1: needs both
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Initially level should not be complete
        $this->assertFalse($document->isLevelComplete());

        // Approve by first user
        $document->approveByUser($level1Approver1->id);
        $document->refresh();
        $this->assertFalse($document->isLevelComplete()); // Still needs second approval
        $this->assertEquals(1, $document->current_level); // Still in level 1

        // Approve by second user - this should complete level 1
        $document->approveByUser($level1Approver2->id);
        $document->refresh();
        $this->assertEquals(2, $document->current_level); // Should move to level 2
    }

    #[Test]
    public function document_moves_to_next_level_when_current_level_completes()
    {
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Approve both level 1 users
        $document->approveByUser($level1Approver1->id);
        $document->approveByUser($level1Approver2->id);

        // Should move to level 2
        $document->refresh();
        $this->assertEquals(2, $document->current_level);
        $this->assertEquals('pending_approval', $document->status);
    }

    #[Test]
    public function document_completes_when_all_levels_approved()
    {
        $level1Approver = User::factory()->create();
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver->id], // Level 1
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Complete level 1
        $document->approveByUser($level1Approver->id);

        // Should move to level 2
        $document->refresh();
        $this->assertEquals(2, $document->current_level);

        // Complete level 2
        $document->approveByUser($level2Approver->id);

        // Should complete the document
        $document->refresh();
        $this->assertEquals('completed', $document->status);
        $this->assertNotNull($document->completed_at);
    }

    #[Test]
    public function document_get_approval_progress_returns_correct_structure()
    {
        $level1Approver1 = User::factory()->create();
        $level1Approver2 = User::factory()->create();
        $level2Approver = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$level1Approver1->id, $level1Approver2->id], // Level 1
                [$level2Approver->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $progress = $document->getApprovalProgress();

        $this->assertEquals(2, count($progress)); // 2 levels

        // Level 1 should be in progress
        $this->assertEquals('in_progress', $progress[0]['status']);
        $this->assertEquals([], $progress[0]['approved']); // Initially no one approved
        $this->assertEquals([$level1Approver1->id, $level1Approver2->id], $progress[0]['pending']); // All pending initially

        // Level 2 should be pending
        $this->assertEquals('pending', $progress[1]['status']);
        $this->assertEquals([], $progress[1]['approved']);
        $this->assertEquals([$level2Approver->id], $progress[1]['pending']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\StepApprover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $approver1;
    private User $approver2;
    private ApprovalFlow $approvalFlow;
    private ApprovalStep $approvalStep;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->approver1 = User::factory()->create(['role' => 'user']);
        $this->approver2 = User::factory()->create(['role' => 'user']);

        // Create approval flow
        $this->approvalFlow = ApprovalFlow::create([
            'name' => 'Test Approval Flow',
            'description' => 'Flow for testing',
            'is_template_default' => true,
            'created_by' => $this->admin->id,
        ]);

        // Create approval step
        $this->approvalStep = ApprovalStep::create([
            'flow_id' => $this->approvalFlow->id,
            'step_order' => 1,
            'step_name' => 'Test Step',
            'step_type' => 'sequential',
            'is_required' => true,
            'can_skip' => false,
            'minimum_approvers' => 1,
        ]);

        // Create step approver
        StepApprover::create([
            'step_id' => $this->approvalStep->id,
            'user_id' => $this->approver1->id,
            'is_backup' => false,
        ]);
    }

    /** @test */
    public function user_can_submit_document_for_approval()
    {
        Storage::fake('public');

        $document = Document::create([
            'title' => 'Test Document',
            'description' => 'Test description',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'draft',
            'created_by' => $this->user->id,
            'qr_position' => 'top-right',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/documents/{$document->id}/approval/submit", [
                'flow_id' => $this->approvalFlow->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'document' => ['id', 'status'],
                'approval' => ['id', 'status'],
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $document->id,
            'flow_id' => $this->approvalFlow->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function approver_can_approve_document()
    {
        // Create document and submit for approval
        $document = Document::create([
            'title' => 'Test Document',
            'description' => 'Test description',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'created_by' => $this->user->id,
            'qr_position' => 'top-right',
        ]);

        $approval = DocumentApproval::create([
            'document_id' => $document->id,
            'flow_id' => $this->approvalFlow->id,
            'status' => 'in_progress',
            'current_step_id' => $this->approvalStep->id,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/{$approval->id}/approve");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'approval' => ['id', 'status'],
                'action' => ['id', 'action'],
            ]);

        $this->assertDatabaseHas('approval_actions', [
            'document_approval_id' => $approval->id,
            'action' => 'approve',
        ]);
    }

    /** @test */
    public function approver_can_reject_document()
    {
        // Create document and submit for approval
        $document = Document::create([
            'title' => 'Test Document',
            'description' => 'Test description',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'created_by' => $this->user->id,
            'qr_position' => 'top-right',
        ]);

        $approval = DocumentApproval::create([
            'document_id' => $document->id,
            'flow_id' => $this->approvalFlow->id,
            'status' => 'in_progress',
            'current_step_id' => $this->approvalStep->id,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/{$approval->id}/reject", [
                'notes' => 'Document needs revision',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('document_approvals', [
            'id' => $approval->id,
            'status' => 'rejected',
            'rejected_by' => $this->approver1->id,
        ]);
    }

    /** @test */
    public function user_can_view_approval_history()
    {
        $document = Document::create([
            'title' => 'Test Document',
            'description' => 'Test description',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'completed',
            'created_by' => $this->user->id,
            'qr_position' => 'top-right',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/documents/{$document->id}/approval/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'status',
                    'document_id',
                    'flow_id',
                ]
            ]);
    }

    /** @test */
    public function approver_can_view_pending_approvals()
    {
        // Create document and submit for approval
        $document = Document::create([
            'title' => 'Test Document',
            'description' => 'Test description',
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'created_by' => $this->user->id,
            'qr_position' => 'top-right',
        ]);

        DocumentApproval::create([
            'document_id' => $document->id,
            'flow_id' => $this->approvalFlow->id,
            'status' => 'in_progress',
            'current_step_id' => $this->approvalFlow->steps->first()->id,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->getJson('/api/approvals/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'document' => ['id', 'title'],
                        'current_step' => ['id', 'step_name'],
                    ]
                ]
            ]);
    }

    /** @test */
    public function document_creation_generates_qr_code()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/documents', [
                'title' => 'Test Document',
                'description' => 'Test description',
                'file' => $file,
                'qr_position' => 'top-left',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'document' => ['id', 'qr_code_path', 'qr_position'],
                'qr_code' => ['path', 'url', 'data'],
            ]);

        $document = Document::find($response->json('document.id'));
        $this->assertNotNull($document->qr_code_path);
        $this->assertEquals('top-left', $document->qr_position);
        $this->assertIsArray($document->qr_code_data);
    }
}

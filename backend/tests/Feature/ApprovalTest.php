<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $approver1;
    protected $approver2;
    protected $approver3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create();
        $this->approver1 = User::factory()->create();
        $this->approver2 = User::factory()->create();
        $this->approver3 = User::factory()->create();
    }

    #[Test]
    public function user_can_get_pending_approvals()
    {
        // Create document with multi-level approval
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
                [$this->approver3->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Initialize level progress
        $document->getLevelProgress();
        $document->save();

        // Approver1 should see this document in pending approvals
        $response = $this->actingAs($this->approver1, 'sanctum')
            ->getJson('/api/approvals/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ])
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.id', $document->id);
    }

    #[Test]
    public function user_can_approve_document_in_current_level()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
                [$this->approver3->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve',
                'comments' => 'Approved by approver 1'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Document approved successfully'
            ]);

        $document->refresh();
        $this->assertEquals(1, $document->current_level); // Still level 1, needs second approval
        $this->assertEquals('pending_approval', $document->status);
    }

    #[Test]
    public function document_moves_to_next_level_when_all_approvers_in_level_approve()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
                [$this->approver3->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // First approval
        $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        // Second approval - should move to level 2
        $this->actingAs($this->approver2, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $document->refresh();
        $this->assertEquals(2, $document->current_level);
        $this->assertEquals('pending_approval', $document->status);
    }

    #[Test]
    public function document_completes_when_all_levels_approved()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id], // Level 1
                [$this->approver2->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Complete level 1
        $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        // Complete level 2
        $this->actingAs($this->approver2, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $document->refresh();
        $this->assertEquals('completed', $document->status);
        $this->assertNotNull($document->completed_at);
    }

    #[Test]
    public function user_can_reject_document()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'reject',
                'comments' => 'Document rejected'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Document rejected successfully'
            ]);

        $document->refresh();
        $this->assertEquals('rejected', $document->status);
        $this->assertNotNull($document->completed_at);
    }

    #[Test]
    public function user_cannot_approve_document_they_are_not_assigned_to()
    {
        $otherUser = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id], // Only approver1 can approve
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $response->assertStatus(403)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to approve this document at this time.'
            ]);
    }

    #[Test]
    public function user_cannot_approve_completed_document()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id],
            ],
            'current_level' => 1,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $response->assertStatus(403)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to approve this document at this time.'
            ]);
    }

    #[Test]
    public function user_can_delegate_approval_to_another_user_in_same_level()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id, $this->approver3->id], // Level 1
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/delegate", [
                'delegate_to' => $this->approver3->id
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Approval delegated successfully'
            ]);

        $document->refresh();
        $progress = $document->getLevelProgress();

        // approver1 should be removed from pending
        $this->assertNotContains($this->approver1->id, $progress['pending']);

        // approver3 should be in pending (if not already approved)
        $this->assertContains($this->approver3->id, $progress['pending']);
    }

    #[Test]
    public function user_cannot_delegate_to_user_not_in_same_level()
    {
        $outsideUser = User::factory()->create();

        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
                [$this->approver3->id] // Level 2
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/delegate", [
                'delegate_to' => $outsideUser->id // Not in level 1
            ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'Delegate user must be in the same approval level.'
            ]);
    }

    #[Test]
    public function user_cannot_delegate_to_themselves()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id],
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        $document->getLevelProgress();
        $document->save();

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/delegate", [
                'delegate_to' => $this->approver1->id // Same as current user
            ]);

        $response->assertStatus(422); // Validation error for 'different'
    }

    #[Test]
    public function pending_approvals_only_show_documents_user_can_approve()
    {
        // Document 1: User is in level 1
        $document1 = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id], // approver1 can approve
                [$this->approver2->id]
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Document 2: User is not in current level
        $document2 = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver2->id], // Only approver2 can approve
                [$this->approver1->id]
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Document 3: Already completed
        $document3 = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id],
            ],
            'current_level' => 1,
            'status' => 'completed',
        ]);

        // Initialize level progress for all documents
        $document1->getLevelProgress();
        $document1->save();
        $document2->getLevelProgress();
        $document2->save();
        $document3->getLevelProgress();
        $document3->save();

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->getJson('/api/approvals/pending');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.id', $document1->id);
    }

    #[Test]
    public function approval_creates_history_record_with_notes()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id],
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // Initialize level progress
        $document->getLevelProgress();
        $document->save();

        $response = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve',
                'comments' => 'Approved with minor changes',
            ]);

        $response->assertStatus(200);

        // Check that approval record was created
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $document->id,
            'approver_id' => $this->approver1->id,
            'action' => 'approved',
            'notes' => 'Approved with minor changes',
            'level' => 1,
        ]);

        // Check that public info includes approval history
        $publicResponse = $this->getJson("/api/documents/{$document->id}/public-info");
        $publicResponse->assertStatus(200)
            ->assertJsonFragment([
                'notes' => 'Approved with minor changes',
            ]);
    }

    #[Test]
    public function user_cannot_approve_document_twice()
    {
        $document = Document::factory()->create([
            'created_by' => $this->user->id,
            'approvers' => [
                [$this->approver1->id, $this->approver2->id], // Level 1
            ],
            'current_level' => 1,
            'status' => 'pending_approval',
        ]);

        // First approval should succeed
        $response1 = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $response1->assertStatus(200);

        // Second approval by same user should fail
        $response2 = $this->actingAs($this->approver1, 'sanctum')
            ->postJson("/api/approvals/documents/{$document->id}/process", [
                'action' => 'approve'
            ]);

        $response2->assertStatus(403)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to approve this document at this time.'
            ]);
    }
}
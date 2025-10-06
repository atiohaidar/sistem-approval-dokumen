<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\StepApprover;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ]);
        }

        // Get regular users
        $users = User::where('role', 'user')->take(3)->get();
        if ($users->count() < 3) {
            $users = collect();
            for ($i = 1; $i <= 3; $i++) {
                $users->push(User::factory()->create([
                    'name' => "Approver User {$i}",
                    'email' => "approver{$i}@example.com",
                    'role' => 'user',
                ]));
            }
        }

        // Create approval flow for documents
        $flow = ApprovalFlow::create([
            'name' => 'Standard Document Approval',
            'description' => 'Standard approval flow for documents with sequential approval',
            'is_template_default' => true,
            'created_by' => $admin->id,
        ]);

        // Step 1: Department Head Approval (Sequential)
        $step1 = ApprovalStep::create([
            'flow_id' => $flow->id,
            'step_order' => 1,
            'step_name' => 'Department Head Approval',
            'step_type' => 'sequential',
            'is_required' => true,
            'can_skip' => false,
            'minimum_approvers' => 1,
        ]);

        StepApprover::create([
            'step_id' => $step1->id,
            'user_id' => $users[0]->id,
            'is_backup' => false,
        ]);

        // Step 2: Manager Approval (Parallel - 2 out of 3 required)
        $step2 = ApprovalStep::create([
            'flow_id' => $flow->id,
            'step_order' => 2,
            'step_name' => 'Manager Approval',
            'step_type' => 'parallel',
            'is_required' => true,
            'can_skip' => false,
            'minimum_approvers' => 2,
        ]);

        foreach ($users as $index => $user) {
            StepApprover::create([
                'step_id' => $step2->id,
                'user_id' => $user->id,
                'is_backup' => false,
            ]);
        }

        // Step 3: Final Director Approval (Sequential)
        $step3 = ApprovalStep::create([
            'flow_id' => $flow->id,
            'step_order' => 3,
            'step_name' => 'Director Approval',
            'step_type' => 'sequential',
            'is_required' => true,
            'can_skip' => false,
            'minimum_approvers' => 1,
        ]);

        StepApprover::create([
            'step_id' => $step3->id,
            'user_id' => $admin->id,
            'is_backup' => false,
        ]);

        // Create a simpler approval flow
        $simpleFlow = ApprovalFlow::create([
            'name' => 'Simple Approval',
            'description' => 'Simple approval flow with single approver',
            'is_template_default' => false,
            'created_by' => $admin->id,
        ]);

        $simpleStep = ApprovalStep::create([
            'flow_id' => $simpleFlow->id,
            'step_order' => 1,
            'step_name' => 'Single Approver',
            'step_type' => 'sequential',
            'is_required' => true,
            'can_skip' => false,
            'minimum_approvers' => 1,
        ]);

        StepApprover::create([
            'step_id' => $simpleStep->id,
            'user_id' => $users[0]->id,
            'is_backup' => false,
        ]);
    }
}

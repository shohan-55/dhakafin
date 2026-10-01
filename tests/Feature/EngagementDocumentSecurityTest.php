<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Engagement;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EngagementDocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_advances_through_controlled_workflow(): void
    {
        $this->withoutVite();
        [$user,$organization] = $this->userWithPermissions([
            'engagements.view','engagements.manage',
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.engagements.store'), [
                'service_type'=>'corporate_tax',
                'title'=>'Corporate tax engagement',
                'start_date'=>'2026-10-01',
                'due_date'=>'2026-11-30',
            ])
            ->assertRedirect();

        $engagement=Engagement::firstOrFail();
        $this->assertSame('requested',$engagement->stage->value);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.engagements.advance',$engagement))
            ->assertRedirect(route('workspace.engagements.show',$engagement));

        $this->assertSame('scoped',$engagement->fresh()->stage->value);
        $this->assertDatabaseHas('audit_events',[
            'organization_id'=>$organization->id,
            'action'=>'engagement.stage_advanced',
        ]);
    }

    public function test_private_document_is_tenant_scoped_and_uploader_cannot_self_review(): void
    {
        Storage::fake('local');
        $this->withoutVite();

        [$uploader,$organization] = $this->userWithPermissions([
            'engagements.view','documents.view','documents.manage','documents.review',
        ]);

        $engagement=Engagement::create([
            'organization_id'=>$organization->id,
            'code'=>'ENG-SEC-001',
            'service_type'=>'audit_support',
            'title'=>'Evidence review',
            'status'=>'active',
            'stage'=>'requested',
        ]);

        $this->actingAs($uploader)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.engagements.documents.store',$engagement),[
                'title'=>'Bank confirmation',
                'document'=>UploadedFile::fake()->create('bank-confirmation.pdf',24,'application/pdf'),
            ])
            ->assertRedirect(route('workspace.engagements.show',$engagement));

        $document=Document::firstOrFail();
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($uploader)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.documents.review',$document),[
                'outcome'=>'approved',
            ])
            ->assertForbidden();

        [$reviewer] = $this->userWithPermissions(
            ['documents.view','documents.review'],
            $organization,
            'reviewer-role'
        );

        $this->actingAs($reviewer)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.documents.review',$document),[
                'outcome'=>'approved',
                'notes'=>'Evidence checked.',
            ])
            ->assertRedirect(route('workspace.engagements.show',$engagement));

        $this->assertDatabaseHas('documents',[
            'id'=>$document->id,
            'review_status'=>'approved',
            'reviewed_by'=>$reviewer->id,
        ]);

        $foreign=Organization::create(['name'=>'Foreign Docs','slug'=>'foreign-docs']);
        $reviewer->organizations()->attach($foreign,['status'=>'active','role'=>'member']);

        $foreignViewRole=Role::create([
            'name'=>'Foreign Document Viewer',
            'slug'=>'foreign-doc-viewer-'.uniqid(),
            'scope'=>'organization',
        ]);
        $foreignViewRole->permissions()->attach(
            Permission::where('slug','documents.view')->firstOrFail()
        );
        $reviewer->roles()->attach($foreignViewRole,['organization_id'=>$foreign->id]);

        $this->actingAs($reviewer)
            ->withSession(['current_organization_id'=>$foreign->id])
            ->get(route('workspace.documents.download',$document))
            ->assertNotFound();
    }

    private function userWithPermissions(
        array $slugs,
        ?Organization $organization=null,
        string $roleSlug='test-role'
    ): array {
        $user=User::factory()->create();
        $organization ??= Organization::create([
            'name'=>'Engagement Co '.uniqid(),
            'slug'=>'engagement-'.uniqid(),
        ]);

        $user->organizations()->syncWithoutDetaching([
            $organization->id=>['status'=>'active','role'=>'member'],
        ]);

        $permissions=collect($slugs)->map(function(string $slug){
            return Permission::firstOrCreate(
                ['slug'=>$slug],
                ['name'=>str($slug)->replace('.',' ')->title()->toString()]
            );
        });

        $role=Role::create([
            'name'=>str($roleSlug)->replace('-',' ')->title()->toString(),
            'slug'=>$roleSlug.'-'.uniqid(),
            'scope'=>'organization',
        ]);

        $role->permissions()->attach($permissions->pluck('id')->all());
        $user->roles()->attach($role,['organization_id'=>$organization->id]);

        return [$user,$organization];
    }
}

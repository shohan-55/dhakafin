<?php

namespace App\Support\Rbac;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

final class SystemRbac
{
    public function ensure(): Collection
    {
        $permissions=collect([
            'workspace.view',
            'organization.manage',
            'users.manage',
            'compliance.view',
            'compliance.manage',
            'tax.view',
            'tax.manage',
            'vat.view',
            'vat.manage',
            'accounting.view',
            'accounting.manage',
            'billing.view',
            'billing.manage',
            'engagements.view',
            'engagements.manage',
            'documents.view',
            'documents.manage',
            'documents.review',
            'reports.view',
            'audit-log.view',
        ])->mapWithKeys(function(string $slug): array {
            $permission=Permission::firstOrCreate(
                ['slug'=>$slug],
                ['name'=>str($slug)->replace('.',' ')->title()->toString()]
            );

            return [$slug=>$permission];
        });

        $map=[
            'owner'=>$permissions->keys()->all(),
            'admin'=>$permissions->keys()->reject(fn(string $p)=>$p==='organization.manage')->all(),
            'manager'=>[
                'workspace.view','compliance.view','compliance.manage',
                'tax.view','tax.manage','vat.view','vat.manage',
                'accounting.view','accounting.manage','billing.view',
                'engagements.view','engagements.manage',
                'documents.view','documents.manage','documents.review','reports.view',
            ],
            'staff'=>[
                'workspace.view','compliance.view','tax.view','vat.view',
                'accounting.view','engagements.view','engagements.manage',
                'documents.view','documents.manage','documents.review','reports.view',
            ],
            'client'=>[
                'workspace.view','compliance.view','tax.view','vat.view',
                'billing.view','engagements.view','documents.view','documents.manage','reports.view',
            ],
        ];

        return collect($map)->mapWithKeys(function(array $allowed,string $slug) use ($permissions): array {
            $role=Role::firstOrCreate(
                ['slug'=>$slug],
                ['name'=>str($slug)->title()->toString(),'scope'=>'organization','is_system'=>true]
            );

            $role->permissions()->sync($permissions->only($allowed)->pluck('id')->all());

            return [$slug=>$role->refresh()];
        });
    }
}

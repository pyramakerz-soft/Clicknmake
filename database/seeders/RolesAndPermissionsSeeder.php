<?php

namespace Database\Seeders;

use File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $permissions = [
        //     'role-list',
        //     'role-create',
        //     'role-edit',
        //     'role-delete'
        // ];

        // Get all model files from the app/Models directory
        $modelFiles = File::allFiles(app_path('Models'));

        foreach ($modelFiles as $file) {
            $modelName = strtolower(pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $permissions[] = "{$modelName}-list";
            $permissions[] = "{$modelName}-create";
            $permissions[] = "{$modelName}-edit";
            $permissions[] = "{$modelName}-delete";
        }

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists())
                Permission::create(['name' => $permission]);
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email'=> 'superadmin@gmail.com',
                'password' => Hash::make('SuperAdmin112'), 
               'super_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        $admin = DB::table('users')->where('email', 'superadmin@gmail.com')->first();
        $subscriptions = [
            [
                'user_id' => $admin->id,
                'name' => 'super admin',
                'quantity' => 1,
                'stripe_id' => 'free',
                'stripe_plan' => 'free',
                'trial_ends_at' => now(),
                'ends_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        $setting = [
            [
                'user_id' => $admin->id,
                'timezone' => 'America/New_York',
            ],
        ];

        $permissions = DB::table('permissions')->get();

        $userPermissions = $permissions->map(function ($permission) use($admin){
            return ['user_id' => $admin->id, 'permission_id' => $permission->id, 'created_at' => now(), 'updated_at' => now()];
        })->toArray();
        DB::table('subscriptions')->insert($subscriptions);
        DB::table('user_permissions')->insert($userPermissions);
        DB::table('settings')->insert($setting);
    }
}

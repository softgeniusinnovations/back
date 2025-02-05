<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Reset cached roles and permissions
        dump('Reset cached roles and permissions ....');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        dump('Table truncate start ....');
        // Truncate Spatie tables to remove existing data;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        dump('Table truncate end ....');


        dump('Permissions Initializations ....');
        // All Permissions list
        $permissions = [
            'dashboard',
            'manage-better',
            'active-better',
            'banned-better',
            'email-unverify',
            'mobile-unverify',
            'kyc-unverify',
            'kyc-pending',
            'with-balance',
            'all-bettors',
            'notification-to-all',
            'sporting-config',
            'manage-categories',
            'manage-league',
            'manage-teams',
            'manage-games',
            'running-games',
            'upcoming-games',
            'ended-games',
            'all-games',
            'bet-placed',
            'pending-bet',
            'won-bet',
            'loss-bet',
            'refund-bet',
            'all-bet',
            'declare-outcomes',
            'pending-outcomes',
            'payment-gateways',
            'autometic-payment',
            'manual-payment',
            'deposits',
            'pending-deposits',
            'approved-deposits',
            'successfull-deposits',
            'rejected-deposits',
            'initiated-deposits',
            'all-deposits',
            'withdrawals',
            'withdrawals-methods',
            'pending-withdrawals',
            'approved-withdrawals',
            'rejected-withdrawals',
            'all-withdrawals',
            'support-ticket',
            'pending-ticket',
            'closed-ticket',
            'answered-ticket',
            'all-ticket',
            'report',
            'transection-log',
            'login-history',
            'notification-history',
            'referral-commissions',
            'referral-setting',
            'general-setting',
            'system-configuration',
            'logo-favicon',
            'extensions',
            'languages',
            'seo-manager',
            'kyc-setting',
            'notification-setting',
            'global-template',
            'email-setting',
            'sms-setting',
            'notification-template',
            'manage-tempalte',
            'manage-section',
            'banner-section',
            'news-section',
            'breadcrumb-section',
            'verification-code-page',
            'contact-us',
            'footer-section',
            'fotget-password-page',
            'kyc-instuctions',
            'login-page',
            'policy-pages',
            'register-page',
            'reset-password-page',
            'social-icons',
            'user-ban-page',
            'maintenance-mode',
            'gdrp-cookie',
            'system',
            'application',
            'server',
            'cache',
            'update',
            'custom-css',
            'report-and-request',
            'agent-area',
            'agent-create',
            'agent-update',
            'agent-view',
            'agent-delete',
            'role-management',
            'role-view',
            'role-create',
            'role-edit',
            'role-delete',
            'permission-view',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'transaction-providers-create',
            'transaction-providers-view',
            'transaction-providers-edit',
            'transaction-providers-delete',
            'commission',
            'create-wallet-number',
            'make-deposit',
            'total-bettor-card',
            'active-bettor-card',
            'email-unverified-bettor-card',
            'mobile-unverified-card',
            'pending-bet-card',
            'pending-deposit-card',
            'pending-withdraw-card',
            'pending-ticket-card',
            'total-deposit-card',
            'deposit-charge-card',
            'total-withdraw-card',
            'withdraw-charge-card',
            'total-bettor-link',
            'active-bettor-link',
            'email-unverified-bettor-link',
            'mobile-unverified-link',
            'pending-bet-link',
            'pending-deposit-link',
            'pending-withdraw-link',
            'pending-ticket-link',
            'total-deposit-link',
            'deposit-charge-link',
            'total-withdraw-link',
            'withdraw-charge-link',
            'monthly-deposit-withdraw-report',
            'transaction-report',
            'login-by-browser',
            'login-by-os',
            'login-by-country',
            'odds-game-fetch',
            'make-bettor-deposit',
            'make-bettor-withdraw',
            'bonus-page',
            'affiliate-page',
            'tram-card',
            'agent-password-change',
            'agent-amount-change'
        ];


        // Create permissions
        dump('Count of existing permissions: ' . Permission::where('guard_name', 'admin')->count());
        dump('Permissions store start ....');
        foreach ($permissions as $permission) {
            $existingPermission = Permission::where('name', $permission)->where('guard_name', 'admin')->first();
            if (!$existingPermission) {
                Permission::create(['name' => $permission, 'guard_name' => 'admin']);
            }
        }
        dump('Permissions store end ....');
        dump('Count of existing permissions: ' . Permission::where('guard_name', 'admin')->count());


        // Roles list
        dump('Role Initializations ....');
        $roles = ['agent', 'cash-agent', 'mob-agent', 'super-admin', 'affiliator', 'support', 'report', 'admin', 'sub-admin'];


        // Create all Roles
        dump('Role Create start ....');
        foreach ($roles as $role) {
            $newRole = Role::create(['name' => $role, 'guard_name' => 'admin']);
            dump('New Role ' . $role . ' Successfully Created');
            if ($role === 'super-admin') {
                $superAdmin = Admin::find(1);
                $superAdmin->assignRole('super-admin');
            }
            if ($role === 'admin' || $role === 'sub-admin') {
                $newRole->syncPermissions([
                    'dashboard',
                    'manage-better',
                    'active-better',
                    'banned-better',
                    'email-unverify',
                    'mobile-unverify',
                    'kyc-unverify',
                    'kyc-pending',
                    'with-balance',
                    'all-bettors',
                    'notification-to-all',
                    'sporting-config',
                    'manage-categories',
                    'manage-league',
                    'manage-teams',
                    'manage-games',
                    'running-games',
                    'upcoming-games',
                    'ended-games',
                    'all-games',
                    'bet-placed',
                    'pending-bet',
                    'won-bet',
                    'loss-bet',
                    'refund-bet',
                    'all-bet',
                    'deposits',
                    'pending-deposits',
                    'approved-deposits',
                    'successfull-deposits',
                    'rejected-deposits',
                    'initiated-deposits',
                    'all-deposits',
                    'withdrawals',
                    'withdrawals-methods',
                    'pending-withdrawals',
                    'approved-withdrawals',
                    'rejected-withdrawals',
                    'all-withdrawals'
                ]);
            }
            if ($role === 'agent' || $role === 'cash-agent' || $role === 'mob-agent') {
                $newRole->syncPermissions([
                    'deposits',
                    'commission',
                    'pending-deposits',
                    'approved-deposits',
                    'successfull-deposits',
                    'rejected-deposits',
                    'initiated-deposits',
                    'all-deposits',
                    'withdrawals',
                    'pending-withdrawals',
                    'approved-withdrawals',
                    'rejected-withdrawals',
                    'all-withdrawals',
                    'make-deposit'
                ]);
                
                if($role === 'cash-agent' || $role === 'mob-agent'){
                    $newRole->syncPermissions([
                        'make-bettor-deposit',
                        'make-bettor-withdraw'
                    ]);
                }
            }
            if ($role === 'support') {
                if (!Admin::where('username', 'support')->exists()) {
                    $maxIdentity = Admin::max('identity');
                    $admin = new Admin();
                    $admin->identity = $maxIdentity + 1;
                    $admin->name = "Support";
                    $admin->email = 'support@no-replay.com';
                    $admin->username = 'support';
                    $admin->country_code = 'BD';
                    $admin->currency = 'BDT';
                    $admin->phone = '880012345678';
                    $admin->balance = 0;
                    $admin->type = 6;
                    $admin->ver_code = Str::random(6);
                    $admin->password = Hash::make('support');
                    $admin->save();
                    $admin->assignRole('support');
                }

                $newRole->givePermissionTo([
                    'support-ticket',
                    'pending-ticket',
                    'closed-ticket',
                    'answered-ticket',
                    'all-ticket'
                ]);
            }
            if ($role === 'report') {
                $newRole->givePermissionTo([
                    'report',
                    'transection-log',
                    'login-history',
                    'notification-history',
                    'referral-commissions'
                ]);
            }
        }

        dump('Admin user sync with model has role start.');
        $allAdmins = Admin::get();
        if (count($allAdmins) > 0) {
            foreach ($allAdmins as $admin) {
                if (is_null($admin->type)) {
                    $admin->assignRole('super-admin');
                    dump('Super admin role assign for ' . $admin->username);
                } else {
                    $role = Role::findOrFail($admin->type);
                    $admin->assignRole($role->name);
                    dump($role->name . ' role assign for ' . $admin->username);
                }
            }
        }
        dump('Admin user sync with model has role end.');
        dump('Successfully Seeder Run Completed');
    }
}

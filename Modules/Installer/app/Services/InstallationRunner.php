<?php

namespace Modules\Installer\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\SaasModeWriter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class InstallationRunner
{
    public function __construct(private EnvironmentWriter $environment, private DatabaseInspector $database, private RequirementChecker $requirements, private InstallationHealth $health) {}

    public function run(array $data): void
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        ignore_user_abort(true);

        if (! $this->requirements->passes()) {
            throw new RuntimeException('Server requirements are not satisfied.');
        }
        $inspection = $this->database->inspect($data);
        if (! $inspection['ok'] || (! $inspection['empty'] && ! $inspection['partial'])) {
            throw new RuntimeException($inspection['message']);
        }

        // Empty database -> plain migrate. Anything else here is a previous
        // FLOW attempt, which gets rebuilt from scratch below.
        $isEmptyDatabase = (bool) $inspection['empty'];

        // The lock stops two people installing at once. It also has to forgive
        // a crashed attempt: if the process is killed mid-run the `finally`
        // below never executes, and without this the same person is locked out
        // of their own retry for the full fifteen minutes. The owner is
        // recorded, so a retry from the same browser session takes the lock
        // back while a genuinely concurrent visitor still cannot.
        $owner = (string) session()->getId();
        $lock = Cache::lock('installer.run', 900);

        if (! $lock->get()) {
            if ($owner !== '' && Cache::get('installer.run.owner') === $owner) {
                Cache::lock('installer.run')->forceRelease();
                $lock = Cache::lock('installer.run', 900);
            }

            if (! $lock->get()) {
                throw new RuntimeException('Another installation is already in progress.');
            }
        }

        Cache::put('installer.run.owner', $owner, 900);

        try {
            // Everything destined for .env is collected here and written ONCE,
            // after the install has actually succeeded.
            //
            // It used to be written up front. That is fatal under
            // `php artisan serve`, which watches .env and restarts the server
            // the moment it changes — killing this very request mid-migration
            // and leaving a half-built database behind (the browser shows
            // ERR_EMPTY_RESPONSE). Deferring the write also gives cleaner
            // semantics: a failed install leaves .env untouched, so the wizard
            // can simply be retried.
            $environment = [
                'APP_NAME' => $data['app_name'], 'APP_URL' => rtrim($data['app_url'], '/'),
                'APP_TIMEZONE' => $data['timezone'],
                // APP_DEMO off is what keeps a real install free of the
                // sample dataset and the 12345678 demo logins.
                'APP_DEMO' => 'false', 'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $data['db_host'], 'DB_PORT' => $data['db_port'], 'DB_DATABASE' => $data['db_database'],
                'DB_USERNAME' => $data['db_username'], 'DB_PASSWORD' => $data['db_password'] ?? '',
            ];

            config(['database.default' => 'mysql', 'database.connections.mysql.host' => $data['db_host'], 'database.connections.mysql.port' => $data['db_port'], 'database.connections.mysql.database' => $data['db_database'], 'database.connections.mysql.username' => $data['db_username'], 'database.connections.mysql.password' => $data['db_password'] ?? '', 'app.name' => $data['app_name'], 'app.timezone' => $data['timezone'], 'app.demo' => false, 'saas.enabled' => $data['mode'] === 'saas']);
            // Run mode is a config value, not an env var — see config/saas.php.
            app(SaasModeWriter::class)->set($data['mode'] === 'saas');
            DB::purge('mysql');
            // Generated in memory rather than with `key:generate`, which would
            // write .env here and trigger the restart described above. It goes
            // into the single write at the end with everything else.
            if (! config('app.key')) {
                $appKey = 'base64:'.base64_encode(random_bytes(32));
                config(['app.key' => $appKey]);
                $environment['APP_KEY'] = $appKey;
            }
            $this->repairInterruptedCreateMigrations();

            // A database that already holds a previous FLOW attempt is
            // rebuilt from scratch rather than migrated on top of. Resuming
            // sounds kinder but is not: `migrate` only runs what is pending, so
            // the seeders then run a second time over rows that already exist
            // and the install fails half way. `migrate:fresh` drops every table
            // and starts clean.
            //
            // This is only ever reached when DatabaseInspector reported the
            // database as `partial` — meaning every row in its `migrations`
            // table is a migration that ships with FLOW. A database with
            // anything unrecognised in it is rejected before we get here, so
            // this can never drop a stranger's tables.
            $rebuilding = ! $isEmptyDatabase;
            $migrateCommand = $rebuilding ? 'migrate:fresh' : 'migrate';

            if (Artisan::call($migrateCommand, ['--force' => true]) !== 0) {
                throw new RuntimeException('Database migration failed. Check the application log for details.');
            }
            if (Artisan::call('db:seed', ['--force' => true]) !== 0) {
                throw new RuntimeException('Database seeding failed. Check the application log for details.');
            }

            $roleSlug = $data['mode'] === 'saas' ? 'saas-super-admin' : 'super-admin';
            $role = Role::where('slug', $roleSlug)->first();
            if (! $role) {
                throw new RuntimeException('The administrator role was not seeded.');
            }
            $user = User::firstOrNew(['email' => $data['admin_email']]);
            $user->name = $data['admin_name'];
            $user->email = $data['admin_email'];
            $user->phone = $data['admin_phone'] ?: null;
            $user->password = Hash::make($data['admin_password']);
            $user->role_id = $role->id;
            $user->permissions = $role->permissions;
            $user->status = 1;
            $user->remember_token = Str::random(10);
            $user->save();

            DB::table('application_installations')->updateOrInsert(['id' => 1], ['mode' => $data['mode'], 'app_version' => config('app.version', 'unknown'), 'completed_at' => now(), 'updated_at' => now(), 'created_at' => now()]);
            // The one and only .env write. Under `artisan serve` this restarts
            // the dev server, but the install is finished by now, so the worst
            // case is that the browser has to retry — /install/complete is
            // already reachable.
            $environment['APP_INSTALLED'] = 'true';
            $this->environment->write($environment);

            Artisan::call('optimize:clear');
            $this->health->forget();
        } finally {
            optional($lock)->release();
            Cache::forget('installer.run.owner');
        }
    }

    private function repairInterruptedCreateMigrations(): void
    {
        if (! Schema::hasTable('migrations')) {
            return;
        }

        $files = array_merge(
            glob(database_path('migrations/*.php')) ?: [],
            glob(base_path('Modules/*/database/migrations/*.php')) ?: [],
        );

        $ran = DB::table('migrations')->pluck('migration')->all();
        $batch = (int) DB::table('migrations')->max('batch') ?: 1;

        foreach ($files as $file) {
            $migration = pathinfo($file, PATHINFO_FILENAME);

            if (in_array($migration, $ran, true)) {
                continue;
            }

            if (! preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_create_(.+)_table$/', $migration, $matches)) {
                continue;
            }

            if (! Schema::hasTable($matches[1])) {
                continue;
            }

            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch,
            ]);
        }
    }
}

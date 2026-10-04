<?php

namespace Modules\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Installer\Http\Requests\InstallRequest;
use Modules\Installer\Services\DatabaseInspector;
use Modules\Installer\Services\InstallationRunner;
use Modules\Installer\Services\RequirementChecker;

class InstallerController extends Controller
{
    public function __construct(private RequirementChecker $requirements, private DatabaseInspector $database) {}

    public function show()
    {
        return view('installer::wizard', ['checks' => $this->requirements->checks(), 'passes' => $this->requirements->passes()]);
    }

    public function testDatabase(Request $request)
    {
        $data = $request->validate(['db_host' => ['required', 'string'], 'db_port' => ['required', 'integer'], 'db_database' => ['required', 'string'], 'db_username' => ['required', 'string'], 'db_password' => ['nullable', 'string']]);

        return response()->json($this->database->inspect($data));
    }

    public function store(InstallRequest $request)
    {
        if (! $this->requirements->passes()) {
            return back()->withErrors(['requirements' => 'Server requirements are not satisfied.']);
        } $data = $request->validated();
        $inspection = $this->database->inspect($data);
        if (! $inspection['ok'] || (! $inspection['empty'] && ! $inspection['partial'])) {
            return back()->withErrors(['database' => $inspection['message']])->withInput($request->except(['db_password', 'admin_password', 'admin_password_confirmation']));
        } session(['installer.payload' => encrypt($data), 'installer.token' => bin2hex(random_bytes(32))]);

        return redirect()->route('installer.review');
    }

    public function review()
    {
        abort_unless(session()->has('installer.payload') && session()->has('installer.token'), 403);
        $data = decrypt(session('installer.payload'));

        return view('installer::review', ['data' => $data, 'token' => session('installer.token')]);
    }

    public function run(Request $request, InstallationRunner $runner)
    {
        $request->validate(['token' => ['required', 'string']]);
        abort_unless(hash_equals((string) session('installer.token'), (string) $request->token), 403);
        $data = decrypt(session('installer.payload'));
        try {
            $runner->run($data);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('installer.review')->withErrors([
                'install' => 'Installation could not finish. If the database contains a previous FLOW attempt, the installer can safely retry from here.',
            ]);
        } session()->forget(['installer.payload', 'installer.token']);

        // Straight to the site. The install is finished and the health check
        // now passes, so any further installer URL would bounce back here
        // anyway — one hop is clearer than a dead-end confirmation page.
        return redirect()->route('home')->with('success', 'Installation complete. Sign in with the administrator account you created.');
    }

    public function complete()
    {
        return view('installer::complete');
    }
}

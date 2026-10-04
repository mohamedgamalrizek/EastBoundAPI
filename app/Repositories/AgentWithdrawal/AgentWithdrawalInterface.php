<?php

namespace App\Repositories\AgentWithdrawal;

interface AgentWithdrawalInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Settlement actions ----
    /** Office accepts the request; the money is reserved but not yet sent. */
    public function approve($id);

    /** Money sent: wallet debited, Dr Agent Payable / Cr Cash-or-Bank. */
    public function pay($id);

    /** Turned down; the amount goes back to the agent's available balance. */
    public function reject($id, ?string $note = null);

    /** Raised by the agent themselves from the portal or the mobile app. */
    public function requestForAgent($request, int $agentId);
}

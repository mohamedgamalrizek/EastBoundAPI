<?php

namespace App\Repositories\AgentCommission;

interface AgentCommissionInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Settlement ----
    /** Credit the commission to the agent's wallet. */
    public function approve($id);

    /** Reverse that credit. */
    public function unapprove($id);
}

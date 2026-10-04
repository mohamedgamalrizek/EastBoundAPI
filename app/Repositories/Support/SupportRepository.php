<?php

namespace App\Repositories\Support;

use App\Models\User;
use App\Models\Customer;
use App\Models\KbArticle;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Repositories\BaseRepository;
use App\Repositories\Support\SupportInterface;

class SupportRepository extends BaseRepository implements SupportInterface
{
    /** Allowed option sets — mirror the support_tickets migration. */
    public const PRIORITIES = ['Low', 'Medium', 'High'];
    public const STATUSES   = ['Open', 'Pending', 'Closed'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer'];

    public function __construct(SupportTicket $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'   => $request->customer_id,
            'assigned_to'   => $request->assigned_to,
            'ticket_no'     => $request->ticket_no,
            'subject'       => $request->subject,
            'customer_name' => $request->customer_name,
            'priority'      => $request->priority,
            'department'    => $request->department,
            'status'        => $request->status,
        ];
    }

    /**
     * BaseRepository::update() has no hook for "field X changed" — overridden
     * here so an assignment or a status change actually tells the person
     * waiting on it, instead of just updating the row silently.
     */
    public function update($request)
    {
        try {
            $ticket = $this->find($request->id);
            $ticket->update($this->data($request));
            $this->logActivity('updated', $ticket);

            if ($ticket->wasChanged('assigned_to') && $ticket->assigned_to) {
                $this->notifyAssignee($ticket);
            }

            if ($ticket->wasChanged('status')) {
                $this->notifyRaiser($ticket);
            }

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /** The staff member now owns this ticket — tell them it landed. */
    private function notifyAssignee(SupportTicket $ticket): void
    {
        if (! $ticket->assignedTo) {
            return;
        }

        Notification::notify($ticket->assignedTo, 'Ticket assigned to you',
            "{$ticket->ticket_no} — {$ticket->subject}", 'general');
    }

    /**
     * A status change is what the customer or agent who raised the ticket is
     * waiting on, so they are the one told — never the assignee, who already
     * knows since they made the change.
     */
    private function notifyRaiser(SupportTicket $ticket): void
    {
        $account = $ticket->customer_id ? $ticket->customer : $ticket->raisedBy;

        if (! $account) {
            return;
        }

        Notification::notify($account, "Ticket {$ticket->status}",
            "{$ticket->ticket_no} — {$ticket->subject} is now {$ticket->status}.", 'general');
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
    }

    public function formData(): array
    {
        return [
            'customers'  => Customer::orderBy('name')->get(['id', 'name']),
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'priorities' => self::PRIORITIES,
            'statuses'   => self::STATUSES,
        ];
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function ticket($id)
    {
        return ['ticket' => SupportTicket::find($id)];
    }

    public function kb()
    {
        return ['articles' => KbArticle::latest()->get()];
    }

}

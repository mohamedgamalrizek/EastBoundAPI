<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\User;

/**
 * A ticket opened by the account itself — a customer from the app, an agent
 * from the portal or the app — rather than keyed in by the desk.
 *
 * Both channels route through here so a self-raised ticket looks the same
 * whichever surface filed it, and so an agent's ticket is attributed through
 * `raised_by`: `customer_id` has no meaning for them, and `assigned_to` is the
 * staff member who will handle it, not the person asking.
 */
class RaiseSupportTicket
{
    /**
     * @param  Customer|User  $account  Who is asking.
     */
    public function __invoke($account, string $subject, ?string $priority = null): SupportTicket
    {
        $isCustomer = $account instanceof Customer;

        $ticket = SupportTicket::create([
            'customer_id'   => $isCustomer ? $account->id : null,
            'raised_by'     => $isCustomer ? null : $account->id,
            'ticket_no'     => 'TKT-' . strtoupper(uniqid()),
            'subject'       => $subject,
            'customer_name' => $account->name,
            'priority'      => $priority ?: 'Medium',
            'status'        => 'Open',
        ]);

        Notification::notify($account, 'Support ticket created',
            "Ticket {$ticket->ticket_no} has been received.", 'general');

        $this->notifyDesk($ticket, $account->name);

        return $ticket;
    }

    /**
     * Tell everyone who can work a ticket that one just landed. Sent to the
     * whole desk rather than one person, so it is not left unanswered
     * because a single inbox went unread — same rule payment claims follow.
     *
     * Keyed off the `support_read` permission itself, not a role name: a
     * role's default permission set and what a given user actually holds
     * can diverge (permissions are a per-user snapshot), so checking the
     * permission is what actually matches who can see the ticket.
     */
    private function notifyDesk(SupportTicket $ticket, string $raisedBy): void
    {
        User::query()
            ->whereJsonContains('permissions', 'support_read')
            ->get()
            ->each(fn ($staff) => Notification::notify(
                $staff,
                'New support ticket',
                "{$ticket->ticket_no} — {$raisedBy}: {$ticket->subject}",
                'general'
            ));
    }
}

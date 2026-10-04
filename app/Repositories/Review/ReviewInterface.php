<?php

namespace App\Repositories\Review;

interface ReviewInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function update($request);

    public function delete($id);

    /** Flip a review's moderation status straight from the list. */
    public function setStatus($id, string $status);

    public function formData(): array;
}

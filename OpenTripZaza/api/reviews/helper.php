<?php
declare(strict_types=1);

function mapReview(array $review): array
{
    return [
        'id' => (int) $review['id'],
        'userId' => (int) $review['user_id'],
        'bookingId' => (int) $review['booking_id'],
        'tripId' => (int) $review['trip_id'],
        'reviewerName' => $review['reviewer_name'],
        'reviewerEmail' => $review['reviewer_email'],
        'tripName' => $review['trip_name'] ?? '',
        'rating' => (int) $review['rating'],
        'content' => $review['content'],
        'status' => $review['status'],
        'createdAt' => $review['created_at'],
        'updatedAt' => $review['updated_at'],
        'deletedAt' => $review['deleted_at'],
    ];
}

function cleanReviewContent(mixed $value): string
{
    $content = trim(strip_tags((string) $value));
    $content = preg_replace('/\s+/u', ' ', $content) ?: '';
    $length = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
    if ($length < 10) {
        throw new InvalidArgumentException('Isi review minimal 10 karakter.');
    }
    if ($length > 500) {
        throw new InvalidArgumentException('Isi review maksimal 500 karakter.');
    }
    return $content;
}

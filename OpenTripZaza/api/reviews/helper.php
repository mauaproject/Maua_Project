<?php
declare(strict_types=1);

function mapReview(array $review): array
{
    return [
        'id' => (int) $review['id'],
        'userId' => (int) $review['user_id'],
        'bookingId' => $review['booking_id'] === null ? null : (int) $review['booking_id'],
        'tripId' => $review['trip_id'] === null ? null : (int) $review['trip_id'],
        'reviewerName' => $review['reviewer_name'],
        'reviewerEmail' => $review['reviewer_email'],
        'tripName' => $review['trip_name'] ?? $review['trip_label'] ?? '',
        'rating' => (int) $review['rating'],
        'content' => $review['content'],
        'status' => $review['status'],
        'createdAt' => $review['created_at'],
        'updatedAt' => $review['updated_at'],
        'deletedAt' => $review['deleted_at'],
    ];
}

function cleanReviewTripLabel(mixed $value): string
{
    $label = trim(strip_tags((string) $value));
    $label = preg_replace('/\s+/u', ' ', $label) ?: '';
    $length = function_exists('mb_strlen') ? mb_strlen($label) : strlen($label);
    if ($length < 2) {
        throw new InvalidArgumentException('Nama trip wajib diisi.');
    }
    if ($length > 190) {
        throw new InvalidArgumentException('Nama trip maksimal 190 karakter.');
    }
    return $label;
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

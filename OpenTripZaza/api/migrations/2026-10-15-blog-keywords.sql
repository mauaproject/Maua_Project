-- Run after 2026-10-14-blog.sql. Existing articles and other tables are preserved.
CREATE TABLE IF NOT EXISTS blog_post_keywords (
    post_id BIGINT UNSIGNED NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    keyword VARCHAR(80) NOT NULL,
    PRIMARY KEY (post_id, sort_order),
    KEY blog_keywords_lookup (keyword, post_id),
    CONSTRAINT blog_keywords_post_fk FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

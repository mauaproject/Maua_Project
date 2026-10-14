-- Blog starts empty. This migration only creates new, independent blog tables.
CREATE TABLE IF NOT EXISTS blog_posts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(180) NOT NULL,
    category VARCHAR(32) NOT NULL DEFAULT 'berita',
    author VARCHAR(100) NOT NULL,
    status ENUM('draft','published','trashed') NOT NULL DEFAULT 'draft',
    title_id VARCHAR(180) NOT NULL,
    excerpt_id VARCHAR(300) NOT NULL DEFAULT '',
    body_id MEDIUMTEXT NOT NULL,
    seo_title_id VARCHAR(70) NOT NULL DEFAULT '',
    seo_description_id VARCHAR(160) NOT NULL DEFAULT '',
    title_en VARCHAR(180) NOT NULL DEFAULT '',
    excerpt_en VARCHAR(300) NOT NULL DEFAULT '',
    body_en MEDIUMTEXT NOT NULL,
    seo_title_en VARCHAR(70) NOT NULL DEFAULT '',
    seo_description_en VARCHAR(160) NOT NULL DEFAULT '',
    cover_url VARCHAR(512) NULL,
    cover_alt VARCHAR(200) NOT NULL DEFAULT '',
    cover_width SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    cover_align ENUM('left','center','right') NOT NULL DEFAULT 'center',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NOT NULL,
    published_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY blog_posts_slug_unique (slug),
    KEY blog_posts_public_index (status, published_at, id),
    KEY blog_posts_category_index (status, category, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_media (
    id CHAR(32) NOT NULL,
    image_url VARCHAR(512) NOT NULL,
    thumbnail_url VARCHAR(512) NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY blog_media_url_unique (image_url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_media (
    post_id BIGINT UNSIGNED NOT NULL,
    media_id CHAR(32) NOT NULL,
    PRIMARY KEY (post_id, media_id),
    CONSTRAINT blog_post_media_post_fk FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    CONSTRAINT blog_post_media_image_fk FOREIGN KEY (media_id) REFERENCES blog_media(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<?php
/**
 * Plugin Name: Auto JSON-LD Structured Data
 * Description: Automatically adds JSON-LD structured data to posts, pages, and products
 * Version: 1.2
 * Author: Nathan Turner
 */

// Add structured data to head
add_action('wp_head', 'autojsonld_add_structured_data');

function autojsonld_add_structured_data() {
    if (!is_singular()) return;

    global $post;

    $schema_type = 'WebPage';
    $post_type = get_post_type($post);
    if (is_singular('post')) {
        $schema_type = 'Article';
    } elseif ($post_type === 'page') {
        $schema_type = 'WebPage';
    } elseif ($post_type === 'product') {
        $schema_type = 'Product';
    }

    $title = get_the_title($post);
    $description = get_the_excerpt($post);
    $url = get_permalink($post);
    $date_published = get_the_date('c', $post);
    $date_modified = get_the_modified_date('c', $post);
    $author_name = get_the_author_meta('display_name', $post->post_author);
    $featured_image = get_the_post_thumbnail_url($post, 'full');

    $jsonld = [
        "@context" => "https://schema.org",
        "@type" => $schema_type,
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => $url
        ],
        "headline" => $title,
        "description" => $description,
        "datePublished" => $date_published,
        "dateModified" => $date_modified,
    ];

    if ($featured_image) {
        $jsonld["image"] = [
            "@type" => "ImageObject",
            "url" => $featured_image
        ];
    }

    if ($schema_type === 'Article') {
        $jsonld["author"] = [
            "@type" => "Person",
            "name" => $author_name
        ];
    }

    if ($schema_type === 'Product') {
        $jsonld["name"] = $title;
        $jsonld["offers"] = [
            "@type" => "Offer",
            "url" => $url,
            "priceCurrency" => "USD",
            "availability" => "https://schema.org/InStock"
        ];
    }

    echo '<script type="application/ld+json">' .
        json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
        '</script>';
}



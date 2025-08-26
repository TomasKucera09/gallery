<?php
/**
 * PhotoGallery CMS - Appearance Configuration
 * 
 * This file contains appearance and theme settings
 */

return [
    // Theme settings
    'theme' => 'auto', // light, dark, auto
    'primary_color' => '#3B82F6',
    'secondary_color' => '#1F2937',
    'accent_color' => '#10B981',
    'background_color' => '#FFFFFF',
    'text_color' => '#1F2937',
    
    // Logo and branding
    'logo' => 'data/logo.png',
    'logo_width' => 'auto',
    'logo_height' => 'auto',
    'favicon' => 'favicon.ico',
    'site_name' => 'PhotoGallery CMS',
    
    // Navigation
    'navbar_style' => 'transparent', // transparent, solid, glass
    'navbar_background' => 'rgba(0, 0, 0, 0.8)',
    'navbar_text_color' => '#FFFFFF',
    'navbar_hover_color' => '#3B82F6',
    
    // Page settings
    'enabled_pages' => [
        'index' => true,
        'gallery' => true,
        'photogallery' => true,
        'about' => false,
        'contact' => false
    ],
    
    // Gallery settings
    'gallery_columns' => [
        'mobile' => 1,
        'tablet' => 2,
        'desktop' => 4
    ],
    'gallery_gap' => '2rem',
    'gallery_animation' => 'fade-in',
    
    // Image settings
    'image_border_radius' => '0.75rem',
    'image_shadow' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
    'image_hover_scale' => 1.05,
    
    // Typography
    'font_family' => [
        'primary' => 'Inter, system-ui, sans-serif',
        'secondary' => 'Great Vibes, cursive',
        'monospace' => 'JetBrains Mono, monospace'
    ],
    'font_sizes' => [
        'xs' => '0.75rem',
        'sm' => '0.875rem',
        'base' => '1rem',
        'lg' => '1.125rem',
        'xl' => '1.25rem',
        '2xl' => '1.5rem',
        '3xl' => '1.875rem',
        '4xl' => '2.25rem',
        '5xl' => '3rem',
        '6xl' => '3.75rem',
        '7xl' => '4.5rem'
    ],
    
    // Spacing
    'spacing' => [
        'xs' => '0.25rem',
        'sm' => '0.5rem',
        'md' => '1rem',
        'lg' => '1.5rem',
        'xl' => '2rem',
        '2xl' => '3rem',
        '3xl' => '4rem'
    ],
    
    // Animations
    'animations' => [
        'duration' => '300ms',
        'easing' => 'cubic-bezier(0.4, 0, 0.2, 1)',
        'enabled' => true
    ],
    
    // Custom CSS
    'custom_css' => '',
    
    // Social media
    'social_links' => [
        'instagram' => 'https://www.instagram.com/yourusername/',
        'facebook' => 'https://www.facebook.com/yourusername/',
        'twitter' => 'https://twitter.com/yourusername/',
        'youtube' => 'https://www.youtube.com/yourusername/'
    ],
    
    // Footer
    'footer_text' => '© Copyright 2025 PhotoGallery CMS. All Rights Reserved.',
    'footer_links' => [
        'privacy' => '/privacy',
        'terms' => '/terms',
        'contact' => '/contact'
    ]
];

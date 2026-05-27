<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'website_name',
                'value' => 'FreshCart',
                'type' => 'text',
                'description' => 'Website name displayed in header and title',
                'is_public' => true,
                'position' => 1,
            ],
            [
                'key' => 'website_title',
                'value' => 'FreshCart - Online Grocery Shopping',
                'type' => 'text',
                'description' => 'Website title for SEO and browser tab',
                'is_public' => true,
                'position' => 2,
            ],
            [
                'key' => 'website_logo',
                'value' => 'images/logo.png',
                'type' => 'image',
                'description' => 'Website logo displayed in header (Recommended: 200x60px, PNG/JPG)',
                'is_public' => true,
                'position' => 3,
            ],
            [
                'key' => 'website_favicon',
                'value' => 'images/favicon.ico',
                'type' => 'image',
                'description' => 'Favicon icon for browser tab (Recommended: 32x32px, ICO/PNG)',
                'is_public' => true,
                'position' => 4,
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@freshcart.com',
                'type' => 'text',
                'description' => 'Contact email for customers',
                'is_public' => true,
                'position' => 5,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+8801234567890',
                'type' => 'text',
                'description' => 'Contact phone number',
                'is_public' => true,
                'position' => 5,
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/freshcart',
                'type' => 'text',
                'description' => 'Facebook page URL',
                'is_public' => true,
                'position' => 7,
            ],
            [
                'key' => 'social_facebook_icon',
                'value' => 'images/social/facebook.svg',
                'type' => 'image',
                'description' => 'Facebook icon (Recommended: 24x24px, SVG/PNG)',
                'is_public' => true,
                'position' => 8,
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/freshcart',
                'type' => 'text',
                'description' => 'Twitter profile URL',
                'is_public' => true,
                'position' => 9,
            ],
            [
                'key' => 'social_twitter_icon',
                'value' => 'images/social/twitter.svg',
                'type' => 'image',
                'description' => 'Twitter icon (Recommended: 24x24px, SVG/PNG)',
                'is_public' => true,
                'position' => 10,
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/freshcart',
                'type' => 'text',
                'description' => 'Instagram profile URL',
                'is_public' => true,
                'position' => 11,
            ],
            [
                'key' => 'social_instagram_icon',
                'value' => 'images/social/instagram.svg',
                'type' => 'image',
                'description' => 'Instagram icon (Recommended: 24x24px, SVG/PNG)',
                'is_public' => true,
                'position' => 12,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

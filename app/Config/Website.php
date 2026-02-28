<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Website extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Website Branding Configuration
     * --------------------------------------------------------------------------
     *
     * This file contains all website branding and logo configuration.
     * Edit these values to easily change your website's logo and header text.
     */

    /**
     * Website Name / Header Title
     * Displayed in the navbar and page title
     */
    public string $siteName = 'Perpustakaan SD Binekas';

    /**
     * Website Logo (Navbar Logo)
     * Path to the logo image shown in the navigation bar
     * Store images in the public folder
     */
    public string $navbarLogo = '/logo.png';

    /**
     * Homepage Logo
     * Path to the logo image shown on the homepage/catalog page
     */
    public string $homepageLogo = '/pattern.png';

    /**
     * Homepage Title
     * Title displayed next to the homepage logo
     */
    public string $homepageTitle = 'Katalog';

    /**
     * Decorative Image on Homepage
     * Background/decorative image shown on the homepage
     */
    public string $homepageDecorativeImage = '/children.png';

    /**
     * Profile Image
     * Default user profile image path
     */
    public string $profileImage = '/profile.jpg';

    /**
     * Latest Books Section Title
     * Title for the "New Books Collection" section on homepage
     */
    public string $latestBooksTitle = 'Koleksi Buku Terbaru';

    /**
     * Background Image (Login Page)
     * Background image for the login page
     */
    public string $loginBackgroundImage = '/background.webp';
}

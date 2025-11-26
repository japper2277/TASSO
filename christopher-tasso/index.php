<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- SEO Meta Tags -->
    <meta name="description" content="Christopher Tasso - New York City based photographer specializing in street photography, portraiture, and fashion. Capturing raw urban moments and fleeting beauty.">
    <meta name="keywords" content="Christopher Tasso, NYC photographer, street photography, fashion photography, portrait photography, New York photographer">
    <meta name="author" content="Christopher Tasso">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Christopher Tasso | Photography">
    <meta property="og:description" content="New York City based photographer specializing in street photography, portraiture, and fashion.">
    <meta property="og:url" content="<?php echo home_url(); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Christopher Tasso | Photography">
    <meta name="twitter:description" content="NYC based photographer - street, portrait, and fashion photography.">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📷</text></svg>">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <header>
        <a href="<?php echo home_url(); ?>" class="logo">CHRISTOPHER TASSO</a>
        <nav class="desktop-nav">
            <ul>
                <li><a href="#" onclick="switchView('street', this)">Street</a></li>
                <li><a href="#" onclick="switchView('portraiture', this)">Portraiture</a></li>
                <li><a href="#" onclick="switchView('fashion', this)">Fashion</a></li>
                <li><a href="#" onclick="switchView('video', this)">Video</a></li>
                <li><a href="#" onclick="switchView('contact', this)">Contact</a></li>
            </ul>
        </nav>
        <div class="hamburger">☰</div>
    </header>

    <div class="mobile-menu">
        <div class="close-menu">×</div>
        <a href="#" onclick="closeMenu(); switchView('street')">Street</a>
        <a href="#" onclick="closeMenu(); switchView('portraiture')">Portraiture</a>
        <a href="#" onclick="closeMenu(); switchView('fashion')">Fashion</a>
        <a href="#" onclick="closeMenu(); switchView('video')">Video</a>
        <a href="#" onclick="closeMenu(); switchView('contact')">Contact</a>
    </div>

    <main id="main-stage">

        <!-- HOME GRID -->
        <div class="grid-container" id="home-view">
            <div class="grid-block" onclick="switchView('street')">
                <img src="https://images.unsplash.com/photo-1485550409059-9afb054cada4?q=80&w=1965&auto=format&fit=crop" alt="Street" loading="lazy">
                <div class="cat-label">Street</div>
            </div>
            <div class="grid-block" onclick="switchView('portraiture')">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=1964&auto=format&fit=crop" alt="Portraiture" loading="lazy">
                <div class="cat-label">Portraiture</div>
            </div>
            <div class="grid-block" onclick="switchView('fashion')">
                <img src="https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=1976&auto=format&fit=crop" alt="Fashion" loading="lazy">
                <div class="cat-label">Fashion</div>
            </div>
            <div class="grid-block" onclick="switchView('video')">
                <video autoplay muted loop playsinline poster="https://via.placeholder.com/800x800/000000/FFFFFF?text=LOADING" src="https://videos.pexels.com/video-files/5309381/5309381-hd_1920_1080_25fps.mp4"></video>
                <div class="cat-label">Video</div>
            </div>
        </div>

        <!-- STREET GALLERY -->
        <div class="gallery-wrap" id="street-view" style="display:none;">
            <div class="arrow-zone left" onclick="scrollGallery('street', 'left')"><div class="arrow-icon">←</div></div>
            <div class="arrow-zone right" onclick="scrollGallery('street', 'right')"><div class="arrow-icon">→</div></div>

            <?php
            $street_query = new WP_Query(array('category_name' => 'street', 'posts_per_page' => -1));
            if ($street_query->have_posts()) : while ($street_query->have_posts()) : $street_query->the_post(); ?>
                <div class="gallery-item">
                    <?php the_post_thumbnail('full'); ?>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <!-- PORTRAITURE GALLERY -->
        <div class="gallery-wrap" id="portraiture-view" style="display:none;">
            <div class="arrow-zone left" onclick="scrollGallery('portraiture', 'left')"><div class="arrow-icon">←</div></div>
            <div class="arrow-zone right" onclick="scrollGallery('portraiture', 'right')"><div class="arrow-icon">→</div></div>

            <?php
            $port_query = new WP_Query(array('category_name' => 'portraiture', 'posts_per_page' => -1));
            if ($port_query->have_posts()) : while ($port_query->have_posts()) : $port_query->the_post(); ?>
                <div class="gallery-item">
                    <?php the_post_thumbnail('full'); ?>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <!-- FASHION GALLERY -->
        <div class="gallery-wrap" id="fashion-view" style="display:none;">
            <div class="arrow-zone left" onclick="scrollGallery('fashion', 'left')"><div class="arrow-icon">←</div></div>
            <div class="arrow-zone right" onclick="scrollGallery('fashion', 'right')"><div class="arrow-icon">→</div></div>

            <?php
            $fashion_query = new WP_Query(array('category_name' => 'fashion', 'posts_per_page' => -1));
            if ($fashion_query->have_posts()) : while ($fashion_query->have_posts()) : $fashion_query->the_post(); ?>
                <div class="gallery-item">
                    <?php the_post_thumbnail('full'); ?>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <!-- VIDEO SECTION -->
        <div class="gallery-wrap" id="video-view" style="display:none;">
            <div class="arrow-zone left" onclick="scrollGallery('video', 'left')"><div class="arrow-icon">←</div></div>
            <div class="arrow-zone right" onclick="scrollGallery('video', 'right')"><div class="arrow-icon">→</div></div>

            <?php
            $video_query = new WP_Query(array('category_name' => 'video', 'posts_per_page' => -1));
            if ($video_query->have_posts()) : while ($video_query->have_posts()) : $video_query->the_post(); ?>
                <div class="gallery-item">
                    <?php the_post_thumbnail('full'); ?>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <!-- CONTACT -->
        <div class="contact-split" id="contact-view">
            <div class="contact-left">
                <img src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?q=80&w=1964&auto=format&fit=crop" alt="Contact Portrait" loading="lazy">
            </div>
            <div class="contact-right">
                <div class="bio-text">
                    Christopher Tasso is a photographer based in New York City, specializing in fashion and street photography. His work captures the raw texture and fleeting moments of urban life.
                </div>
                <div class="contact-hero">
                    <a href="mailto:tassochristopher@gmail.com" class="email-link">tassochristopher@gmail.com</a>
                    <div class="phone-text">+1 (516) 761-5901</div>
                </div>
                <form class="minimal-form" action="https://formsubmit.co/tassochristopher@gmail.com" method="POST">
                    <input type="hidden" name="_subject" value="New message from portfolio">
                    <input type="hidden" name="_captcha" value="false">
                    <div class="form-group"><input type="text" name="name" placeholder="NAME" required></div>
                    <div class="form-group"><input type="email" name="email" placeholder="EMAIL" required></div>
                    <div class="form-group"><textarea name="message" rows="3" placeholder="MESSAGE" required></textarea></div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>

    </main>

    <footer>
        <div class="copyright">&copy; <?php echo date('Y'); ?> CHRISTOPHER TASSO</div>
        <div class="footer-icons">
            <a href="mailto:tassochristopher@gmail.com" class="icon-link">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </a>
            <a href="https://www.instagram.com/peekhere" target="_blank" class="icon-link">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg>
            </a>
        </div>
    </footer>

    <script>
        // --- MOBILE MENU ---
        const hamburger = document.querySelector('.hamburger');
        const mobileMenu = document.querySelector('.mobile-menu');
        const closeBtn = document.querySelector('.close-menu');
        hamburger.addEventListener('click', () => mobileMenu.classList.add('active'));
        function closeMenu() { mobileMenu.classList.remove('active'); }
        closeBtn.addEventListener('click', closeMenu);

        // --- VIEW SWITCHING ---
        const mainStage = document.getElementById('main-stage');
        const homeView = document.getElementById('home-view');
        const contactView = document.getElementById('contact-view');
        const navLinks = document.querySelectorAll('.desktop-nav a');
        const allGalleries = document.querySelectorAll('.gallery-wrap');

        function switchView(viewName, element) {
            navLinks.forEach(link => link.classList.remove('active-link'));
            if(element) element.classList.add('active-link');

            mainStage.classList.add('fading');

            setTimeout(() => {
                // Hide everything first
                homeView.style.display = 'none';
                contactView.style.display = 'none';
                contactView.classList.remove('visible');
                allGalleries.forEach(el => el.style.display = 'none');

                if(viewName === 'contact') {
                    contactView.style.display = 'flex';
                    setTimeout(() => contactView.classList.add('visible'), 50);
                } else if (['street', 'portraiture', 'fashion', 'video'].includes(viewName)) {
                    document.getElementById(viewName + '-view').style.display = 'block';
                } else {
                    homeView.style.display = 'grid';
                }

                mainStage.scrollTop = 0;
                mainStage.classList.remove('fading');
            }, 400);
        }

        // --- GALLERY SCROLL ---
        function getActiveGallery() {
            return Array.from(document.querySelectorAll('.gallery-wrap')).find(el => el.style.display === 'block');
        }

        window.addEventListener('wheel', (evt) => {
            const activeGallery = getActiveGallery();
            if(window.innerWidth > 900 && activeGallery) {
                if (Math.abs(evt.deltaY) > Math.abs(evt.deltaX)) {
                    evt.preventDefault();
                    activeGallery.scrollLeft += evt.deltaY * 1.5;
                }
            }
        }, {passive: false});

        function scrollGallery(category, direction) {
            const activeGallery = document.getElementById(category + '-view');
            const scrollAmount = window.innerWidth * 0.6;
            if(direction === 'right') activeGallery.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            else activeGallery.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        }
    </script>

    <?php wp_footer(); ?>
</body>
</html>

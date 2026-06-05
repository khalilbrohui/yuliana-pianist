/* ==========================================================================
   CADENZA MUSIC ACADEMY - FRONTEND AJAX ENGINE & INTERACTION
   ========================================================================== */

$(document).ready(function() {
    // Initialize standard features
    initGlobalEvents();
    bindAjaxLinks();
    initPageFeatures();

    // Listen to browser Back/Forward navigation
    window.onpopstate = function(event) {
        loadPage(window.location.pathname, false);
    };
});

/**
 * Global navigation, scroll and click handlers
 */
function initGlobalEvents() {
    // Mobile navigation menu toggle
    $(document).on('click', '#mobileNavToggle', function() {
        $(this).toggleClass('active');
        $('#navbarMenu').toggleClass('active');
    });

    // Close menu when a link is clicked
    $(document).on('click', '#navbarMenu a:not(.dropdown-toggle)', function() {
        $('#mobileNavToggle').removeClass('active');
        $('#navbarMenu').removeClass('active');
    });

    // Scroll-to-top button behavior
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#btnScrollTop').addClass('visible');
            $('#mainHeader').addClass('scrolled');
        } else {
            $('#btnScrollTop').removeClass('visible');
            $('#mainHeader').removeClass('scrolled');
        }
    });

    $(document).on('click', '#btnScrollTop', function() {
        $('html, body').animate({ scrollTop: 0 }, 600);
    });

    // Hover effect adjustments for header dropdowns on mobile
    $(document).on('click', '.dropdown-toggle', function(e) {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            $(this).next('.dropdown-menu').slideToggle(200);
        }
    });
}

/**
 * Intercept all qualifying anchor tag clicks to load content via AJAX without page reload
 */
function bindAjaxLinks() {
    $(document).on('click', 'a', function(e) {
        const href = $(this).attr('href');
        
        // Skip external links, anchors, empty links, logout or setups
        if (!href || 
            href.startsWith('http') && !href.includes(window.location.host) || 
            href.startsWith('mailto:') || 
            href.startsWith('tel:') || 
            href.startsWith('https://wa.me') ||
            href.includes('#') && href.split('#')[0] === window.location.pathname ||
            href.includes('logout') ||
            href.includes('setup.php')
        ) {
            return;
        }

        e.preventDefault();
        loadPage(href, true);
    });
}

/**
 * Core AJAX page loader with history push and GSAP animations
 */
function loadPage(url, pushState = true) {
    // Start page exit transition
    gsap.to('#ajaxPageContainer', {
        opacity: 0,
        y: -15,
        duration: 0.2,
        onComplete: function() {
            // Fetch content via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Extract HTML blocks
                    const newTitle = $(response).filter('title').text() || 'Cadenza Music Academy';
                    const newContent = $(response).find('#ajaxPageContainer').html() || $(response).filter('#ajaxPageContainer').html();
                    
                    if (!newContent) {
                        // If output didn't wrap in #ajaxPageContainer (fallback)
                        window.location.href = url;
                        return;
                    }

                    // Update DOM
                    document.title = newTitle;
                    $('#ajaxPageContainer').html(newContent);
                    
                    // Push states
                    if (pushState) {
                        window.history.pushState({ path: url }, newTitle, url);
                    }

                    // Scroll to top
                    window.scrollTo(0, 0);

                    // Re-render and initialize components specific to the new page view
                    initPageFeatures();

                    // Re-initialize 3D piano canvas if loaded homepage
                    if ($('#piano3dCanvas').length > 0 && typeof init3DPiano === 'function') {
                        init3DPiano();
                    }

                    // Page entry animation
                    gsap.to('#ajaxPageContainer', {
                        opacity: 1,
                        y: 0,
                        duration: 0.35,
                        ease: 'power2.out'
                    });
                },
                error: function() {
                    window.location.href = url; // Fail-soft reload
                }
            });
        }
    });
}

/**
 * Initialize dynamic features for forms, accordions, calendar, etc.
 */
function initPageFeatures() {
    initFaqAccordion();
    initBookingCalendar();
    initBlogSearch();
    initFormSubmissions();
    initAdminActions();
    initGsapReveals();
}

/**
 * FAQ Collapsible Accordion logic
 */
function initFaqAccordion() {
    $(document).off('click', '.faq-header').on('click', '.faq-header', function() {
        const item = $(this).closest('.faq-item');
        item.siblings('.faq-item').removeClass('active').find('.faq-body').slideUp(250);
        item.toggleClass('active').find('.faq-body').slideToggle(250);
    });
}

/**
 * Scheduling Calendar Widget handling
 */
function initBookingCalendar() {
    if ($('.calendar-grid').length === 0) return;

    let selectedDate = '';
    let selectedSlot = '';

    // Day selection listener
    $(document).off('click', '.calendar-day:not(.disabled)').on('click', '.calendar-day:not(.disabled)', function() {
        $('.calendar-day').removeClass('selected');
        $(this).addClass('selected');
        
        selectedDate = $(this).attr('data-date');
        $('#selected_date_input').val(selectedDate);
        
        // Reset slot selection
        selectedSlot = '';
        $('#selected_slot_input').val('');
        $('.slot-pill').removeClass('selected');

        // Dynamically enable slot selection container
        $('.slots-container').slideDown(250);
    });

    // Time Slot pill listener
    $(document).off('click', '.slot-pill:not(.disabled)').on('click', '.slot-pill:not(.disabled)', function() {
        $('.slot-pill').removeClass('selected');
        $(this).addClass('selected');
        
        selectedSlot = $(this).attr('data-slot');
        $('#selected_slot_input').val(selectedSlot);
    });
}

/**
 * Blog system category filter and search
 */
function initBlogSearch() {
    if ($('#blogSearchInput').length === 0) return;

    $(document).off('keyup', '#blogSearchInput').on('keyup', '#blogSearchInput', function() {
        const query = $(this).val().toLowerCase();
        $('.blog-card').each(function() {
            const title = $(this).find('h3').text().toLowerCase();
            const desc = $(this).find('p').text().toLowerCase();
            if (title.includes(query) || desc.includes(query)) {
                $(this).fadeIn(200);
            } else {
                $(this).fadeOut(200);
            }
        });
    });

    // Category click filters
    $(document).off('click', '.category-btn').on('click', '.category-btn', function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');

        const cat = $(this).attr('data-category').toLowerCase();
        $('.blog-card').each(function() {
            const cardCat = $(this).attr('data-category').toLowerCase();
            if (cat === 'all' || cardCat === cat) {
                $(this).fadeIn(200);
            } else {
                $(this).fadeOut(200);
            }
        });
    });
}

/**
 * Intercept and process form submissions via AJAX
 */
function initFormSubmissions() {
    // AJAX Trial Lesson Booking Form
    $(document).off('submit', '#trialBookingForm').on('submit', '#trialBookingForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=book_slot');
    });

    // AJAX Student Registration / Signup Form
    $(document).off('submit', '#studentRegisterForm').on('submit', '#studentRegisterForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=register');
    });

    // AJAX Student / Admin Login Form
    $(document).off('submit', '#studentLoginForm').on('submit', '#studentLoginForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=login', function(response) {
            if (response.success) {
                setTimeout(function() {
                    loadPage(response.redirect_url, true);
                }, 1000);
            }
        });
    });

    // AJAX Contact Form
    $(document).off('submit', '#academyContactForm').on('submit', '#academyContactForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=contact_submit');
    });
}

/**
 * Standard AJAX Form Submission Helper
 */
function submitAjaxForm(form, url, callback = null) {
    const alertBox = form.find('.form-alert');
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnHtml = submitBtn.html();

    alertBox.fadeOut(100);
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

    $.ajax({
        url: url,
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalBtnHtml);
            
            if (response.success) {
                alertBox.removeClass('danger').addClass('success').html(response.message).fadeIn(250);
                if (form.attr('id') !== 'studentLoginForm') {
                    form[0].reset();
                    $('.slot-pill').removeClass('selected');
                    $('.calendar-day').removeClass('selected');
                }
            } else {
                alertBox.removeClass('success').addClass('danger').html(response.message).fadeIn(250);
            }

            if (callback) callback(response);
        },
        error: function() {
            submitBtn.prop('disabled', false).html(originalBtnHtml);
            alertBox.removeClass('success').addClass('danger').html('An unexpected error occurred. Please try again.').fadeIn(250);
        }
    });
}

/**
 * Admin Panel CRUD operations & tab configurations
 */
function initAdminActions() {
    if ($('.dashboard-layout').length === 0) return;

    // Sidebar tab swapping
    $(document).off('click', '.sidebar-menu-item').on('click', '.sidebar-menu-item', function(e) {
        e.preventDefault();
        $('.sidebar-menu-item').removeClass('active');
        $(this).addClass('active');

        const panelId = $(this).find('a').attr('href');
        $('.dashboard-panel').removeClass('active');
        $(panelId).addClass('active');
    });

    // Approve student action
    $(document).off('click', '.btn-approve-student').on('click', '.btn-approve-student', function() {
        const studentId = $(this).attr('data-id');
        const row = $(this).closest('tr');
        
        $.post('/api.php?action=admin_approve_student', { id: studentId }, function(res) {
            if (res.success) {
                row.find('.status-badge').removeClass('pending rejected').addClass('approved').text('approved');
                row.find('.btn-approve-student').remove();
            }
        }, 'json');
    });

    // Cancel booking action
    $(document).off('click', '.btn-cancel-booking').on('click', '.btn-cancel-booking', function() {
        const bookingId = $(this).attr('data-id');
        const row = $(this).closest('tr');

        if (confirm('Are you sure you want to cancel this booking slot?')) {
            $.post('/api.php?action=admin_cancel_booking', { id: bookingId }, function(res) {
                if (res.success) {
                    row.find('.status-badge').removeClass('pending confirmed').addClass('cancelled').text('cancelled');
                }
            }, 'json');
        }
    });

    // Save Blog post action via AJAX
    $(document).off('submit', '#adminAddBlogForm').on('submit', '#adminAddBlogForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=admin_add_blog', function(res) {
            if (res.success) {
                setTimeout(function() {
                    loadPage('/admin', true);
                }, 1500);
            }
        });
    });

    // Save Course CMS details via AJAX
    $(document).off('submit', '#adminEditCourseForm').on('submit', '#adminEditCourseForm', function(e) {
        e.preventDefault();
        submitAjaxForm($(this), '/api.php?action=admin_edit_course', function(res) {
            if (res.success) {
                setTimeout(function() {
                    loadPage('/admin', true);
                }, 1500);
            }
        });
    });
}

/**
 * GSAP card reveal / entry transitions on scroll
 */
function initGsapReveals() {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    // Fade in text and grid elements
    gsap.utils.toArray('.reveal').forEach(function(elem) {
        gsap.from(elem, {
            scrollTrigger: {
                trigger: elem,
                start: 'top 85%',
                toggleActions: 'play none none none'
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            ease: 'power2.out'
        });
    });
}

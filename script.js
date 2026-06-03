/* ==========================================================================
   KHALIL AHMED - PORTFOLIO INTERACTIVE LOGIC
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Navigation Scrolled Effect
    const header = document.querySelector('header');
    const scrollThreshold = 50;

    window.addEventListener('scroll', () => {
        if (window.scrollY > scrollThreshold) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // 2. Mobile Menu Toggle
    const menuBtn = document.querySelector('.menu-btn');
    const nav = document.querySelector('nav');

    if (menuBtn && nav) {
        menuBtn.addEventListener('click', () => {
            nav.classList.toggle('active');
            const icon = menuBtn.querySelector('i');
            if (icon) {
                if (nav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        // Close nav when clicking a link
        const navLinks = nav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('active');
                const icon = menuBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });
    }

    // 3. Scrollspy - Highlight Active Link on Scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('nav ul li a');

    function scrollSpy() {
        const scrollY = window.pageYOffset;

        sections.forEach(current => {
            const sectionHeight = current.offsetHeight;
            const sectionTop = current.offsetTop - 120; // offset for nav height
            const sectionId = current.getAttribute('id');
            const link = document.querySelector(`nav ul li a[href*=${sectionId}]`);

            if (link) {
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }

    window.addEventListener('scroll', scrollSpy);

    // 4. Hero Section Typing Effect
    const typingSpan = document.getElementById('typing-text');
    const professions = [
        "Senior Flutter Developer",
        "Cross-Platform Mobile Expert",
        "BLoC & Clean Architecture Specialist"
    ];
    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;

    function type() {
        const currentWord = professions[wordIndex];
        
        if (isDeleting) {
            typingSpan.textContent = currentWord.substring(0, charIndex - 1);
            charIndex--;
            typingSpeed = 50; // delete faster
        } else {
            typingSpan.textContent = currentWord.substring(0, charIndex + 1);
            charIndex++;
            typingSpeed = 100; // standard typing
        }

        if (!isDeleting && charIndex === currentWord.length) {
            typingSpeed = 2000; // Pause at end of word
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % professions.length;
            typingSpeed = 500; // short pause before typing next
        }

        setTimeout(type, typingSpeed);
    }

    if (typingSpan) {
        type();
    }

    // 5. Scroll Reveal Animation using IntersectionObserver
    const reveals = document.querySelectorAll('.reveal');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Unobserve once shown
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    });

    reveals.forEach(element => {
        revealObserver.observe(element);
    });

    // 6. Dynamic Stats Count-Up Animation
    const statsSection = document.getElementById('about');
    const statNumbers = document.querySelectorAll('.stat-number');
    let animated = false;

    function animateStats() {
        statNumbers.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-target'), 10);
            const suffix = stat.getAttribute('data-suffix') || '';
            let current = 0;
            const duration = 1500; // ms
            const stepTime = Math.abs(Math.floor(duration / target));

            const timer = setInterval(() => {
                current += 1;
                stat.innerHTML = current + `<span>${suffix}</span>`;
                if (current >= target) {
                    clearInterval(timer);
                    stat.innerHTML = target + `<span>${suffix}</span>`;
                }
            }, stepTime);
        });
    }

    const statsObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !animated) {
                animateStats();
                animated = true;
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    if (statsSection) {
        statsObserver.observe(statsSection);
    }

    // 7. Projects Filter Logic
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');

            projectCards.forEach(card => {
                const tags = card.getAttribute('data-tags').split(' ');
                
                if (filterValue === 'all' || tags.includes(filterValue)) {
                    card.style.display = 'flex';
                    // Trigger fade-in animation
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(15px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });

    // 8. Contact Form Handling (Simulated Submission)
    const contactForm = document.getElementById('portfolioContactForm');

    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Simple validation check
            const nameInput = document.getElementById('formName');
            const emailInput = document.getElementById('formEmail');
            const messageInput = document.getElementById('formMessage');
            
            if (!nameInput.value || !emailInput.value || !messageInput.value) {
                alert('Please fill out all required fields.');
                return;
            }

            // Transform submit button state
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending message...';

            // Simulate server network delay
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Message Sent!';
                submitBtn.style.background = 'linear-gradient(135deg, #00f2fe 0%, #4facfe 100%)';
                
                alert(`Thank you, ${nameInput.value}! Your simulated message was sent successfully. Khalil will reach out shortly.`);
                
                // Reset form
                contactForm.reset();

                // Re-enable button after cooldown
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.background = '';
                    submitBtn.innerHTML = originalBtnText;
                }, 3000);

            }, 1800);
        });
    }

    // 9. Scroll to Top Button Visibility
    const scrollTopBtn = document.querySelector('.scroll-top');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            scrollTopBtn.classList.add('visible');
        } else {
            scrollTopBtn.classList.remove('visible');
        }
    });

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

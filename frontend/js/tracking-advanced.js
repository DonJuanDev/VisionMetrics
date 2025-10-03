/**
 * VisionMetrics Advanced Tracking Script
 * Includes: Scroll depth, session recording, form tracking, interactions
 */

(function() {
    'use strict';
    
    const VisionMetrics = {
        config: {
            apiKey: null,
            endpoint: '/track.php',
            debug: false,
            recordSessions: true,
            trackScrollDepth: true,
            trackFormFields: true,
            trackClicks: true,
            trackRageClicks: true,
            trackPerformance: true
        },
        
        sessionData: {
            sessionId: null,
            visitorId: null,
            events: [],
            scrollDepths: new Set(),
            formFields: {},
            interactions: [],
            performance: {}
        },

        init: function(apiKey, options = {}) {
            this.config = { ...this.config, ...options, apiKey };
            this.sessionData.sessionId = this.generateSessionId();
            this.sessionData.visitorId = this.getOrCreateVisitorId();
            
            this.setupTracking();
            this.trackPageView();
        },

        // Generate unique IDs
        generateSessionId: function() {
            return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        getOrCreateVisitorId: function() {
            let visitorId = localStorage.getItem('vm_visitor_id');
            if (!visitorId) {
                visitorId = 'visitor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('vm_visitor_id', visitorId);
            }
            return visitorId;
        },

        // Setup all tracking
        setupTracking: function() {
            if (this.config.trackScrollDepth) this.trackScrollDepth();
            if (this.config.trackFormFields) this.trackForms();
            if (this.config.trackClicks) this.trackClicks();
            if (this.config.trackRageClicks) this.trackRageClicks();
            if (this.config.trackPerformance) this.trackPerformance();
            
            // Track exit intent
            document.addEventListener('mouseleave', (e) => {
                if (e.clientY < 0) {
                    this.track('exit_intent', { page: window.location.href });
                }
            });

            // Track time on page
            window.addEventListener('beforeunload', () => {
                const timeOnPage = Math.round((Date.now() - this.sessionData.pageLoadTime) / 1000);
                this.track('page_exit', { 
                    time_on_page: timeOnPage,
                    scroll_depth: this.sessionData.maxScrollDepth || 0
                });
            });

            this.sessionData.pageLoadTime = Date.now();
        },

        // Track page view with enhanced data
        trackPageView: function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            this.track('page_view', {
                page_url: window.location.href,
                page_title: document.title,
                referrer: document.referrer,
                utm_source: urlParams.get('utm_source'),
                utm_medium: urlParams.get('utm_medium'),
                utm_campaign: urlParams.get('utm_campaign'),
                utm_term: urlParams.get('utm_term'),
                utm_content: urlParams.get('utm_content'),
                fbclid: urlParams.get('fbclid'),
                gclid: urlParams.get('gclid'),
                ttclid: urlParams.get('ttclid'),
                msclkid: urlParams.get('msclkid'),
                gbraid: urlParams.get('gbraid'),
                wbraid: urlParams.get('wbraid'),
                // Device info
                screen_width: screen.width,
                screen_height: screen.height,
                viewport_width: window.innerWidth,
                viewport_height: window.innerHeight,
                device_type: this.getDeviceType(),
                browser: this.getBrowserInfo(),
                os: this.getOS(),
                language: navigator.language,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                // Session info
                session_id: this.sessionData.sessionId,
                visitor_id: this.sessionData.visitorId,
                is_returning: this.isReturningVisitor()
            });
        },

        // Scroll depth tracking
        trackScrollDepth: function() {
            const depths = [25, 50, 75, 100];
            let maxDepth = 0;

            window.addEventListener('scroll', this.throttle(() => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = Math.round((scrollTop / docHeight) * 100);

                this.sessionData.maxScrollDepth = Math.max(maxDepth, scrollPercent);

                depths.forEach(depth => {
                    if (scrollPercent >= depth && !this.sessionData.scrollDepths.has(depth)) {
                        this.sessionData.scrollDepths.add(depth);
                        this.track('scroll_depth', { 
                            depth: depth,
                            page: window.location.href 
                        });
                    }
                });

                maxDepth = Math.max(maxDepth, scrollPercent);
            }, 500));
        },

        // Form tracking
        trackForms: function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach((form, index) => {
                const formId = form.id || form.name || `form_${index}`;
                
                // Track form start
                let formStarted = false;
                form.addEventListener('focusin', () => {
                    if (!formStarted) {
                        formStarted = true;
                        this.track('form_start', { form_id: formId });
                    }
                });

                // Track individual fields
                const fields = form.querySelectorAll('input, textarea, select');
                fields.forEach(field => {
                    field.addEventListener('blur', () => {
                        const fieldName = field.name || field.id;
                        if (fieldName && field.value) {
                            // Don't send sensitive values, just track that field was filled
                            this.track('form_field_filled', {
                                form_id: formId,
                                field_name: fieldName,
                                field_type: field.type
                            });
                        }
                    });
                });

                // Track form submission
                form.addEventListener('submit', (e) => {
                    const formData = new FormData(form);
                    const data = {
                        form_id: formId,
                        form_url: window.location.href
                    };

                    // Capture email, phone, name if present
                    for (let [key, value] of formData.entries()) {
                        if (key.match(/email/i)) data.email = value;
                        if (key.match(/phone|tel|celular|whatsapp/i)) data.phone = value;
                        if (key.match(/name|nome/i) && !data.name) data.name = value;
                    }

                    this.track('form_submit', data);
                });
            });
        },

        // Click tracking
        trackClicks: function() {
            document.addEventListener('click', (e) => {
                const target = e.target.closest('a, button');
                if (!target) return;

                const data = {
                    element_type: target.tagName.toLowerCase(),
                    element_text: target.textContent.trim().substring(0, 100),
                    element_id: target.id,
                    element_class: target.className,
                    page: window.location.href
                };

                // Track external links
                if (target.tagName === 'A') {
                    const href = target.getAttribute('href');
                    if (href && href.startsWith('http') && !href.includes(window.location.hostname)) {
                        data.link_type = 'external';
                        data.destination = href;
                        this.track('external_link_click', data);
                    }

                    // Track mailto
                    if (href && href.startsWith('mailto:')) {
                        data.email = href.replace('mailto:', '');
                        this.track('email_click', data);
                    }

                    // Track tel
                    if (href && href.startsWith('tel:')) {
                        data.phone = href.replace('tel:', '');
                        this.track('phone_click', data);
                    }

                    // Track WhatsApp
                    if (href && href.includes('wa.me') || href.includes('whatsapp')) {
                        this.track('whatsapp_click', data);
                    }
                }

                // Track CTA buttons
                if (target.classList.contains('cta') || target.classList.contains('btn-primary')) {
                    this.track('cta_click', data);
                }
            });
        },

        // Rage click detection
        trackRageClicks: function() {
            let clicks = [];
            document.addEventListener('click', (e) => {
                const now = Date.now();
                clicks.push({ time: now, x: e.clientX, y: e.clientY });
                
                // Remove clicks older than 2 seconds
                clicks = clicks.filter(c => now - c.time < 2000);
                
                // Detect rage (5+ clicks in same area within 2 seconds)
                if (clicks.length >= 5) {
                    const avgX = clicks.reduce((sum, c) => sum + c.x, 0) / clicks.length;
                    const avgY = clicks.reduce((sum, c) => sum + c.y, 0) / clicks.length;
                    
                    // Check if all clicks are within 50px radius
                    const isRage = clicks.every(c => 
                        Math.abs(c.x - avgX) < 50 && Math.abs(c.y - avgY) < 50
                    );
                    
                    if (isRage) {
                        this.track('rage_click', {
                            clicks: clicks.length,
                            x: Math.round(avgX),
                            y: Math.round(avgY),
                            page: window.location.href
                        });
                        clicks = []; // Reset
                    }
                }
            });
        },

        // Performance tracking
        trackPerformance: function() {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    if (window.performance && window.performance.timing) {
                        const timing = performance.timing;
                        const loadTime = timing.loadEventEnd - timing.navigationStart;
                        const domReady = timing.domContentLoadedEventEnd - timing.navigationStart;
                        const firstPaint = performance.getEntriesByType('paint')[0]?.startTime || 0;

                        this.track('performance', {
                            load_time: loadTime,
                            dom_ready: domReady,
                            first_paint: Math.round(firstPaint),
                            dns_time: timing.domainLookupEnd - timing.domainLookupStart,
                            tcp_time: timing.connectEnd - timing.connectStart,
                            request_time: timing.responseEnd - timing.requestStart,
                            page: window.location.href
                        });
                    }

                    // Core Web Vitals
                    if ('PerformanceObserver' in window) {
                        // LCP - Largest Contentful Paint
                        new PerformanceObserver((list) => {
                            const entries = list.getEntries();
                            const lastEntry = entries[entries.length - 1];
                            this.track('core_web_vitals', {
                                metric: 'LCP',
                                value: Math.round(lastEntry.renderTime || lastEntry.loadTime),
                                rating: lastEntry.renderTime < 2500 ? 'good' : lastEntry.renderTime < 4000 ? 'needs_improvement' : 'poor'
                            });
                        }).observe({ entryTypes: ['largest-contentful-paint'] });

                        // FID - First Input Delay
                        new PerformanceObserver((list) => {
                            const entries = list.getEntries();
                            entries.forEach(entry => {
                                this.track('core_web_vitals', {
                                    metric: 'FID',
                                    value: Math.round(entry.processingStart - entry.startTime),
                                    rating: entry.processingStart - entry.startTime < 100 ? 'good' : entry.processingStart - entry.startTime < 300 ? 'needs_improvement' : 'poor'
                                });
                            });
                        }).observe({ entryTypes: ['first-input'] });

                        // CLS - Cumulative Layout Shift
                        let clsScore = 0;
                        new PerformanceObserver((list) => {
                            list.getEntries().forEach(entry => {
                                if (!entry.hadRecentInput) {
                                    clsScore += entry.value;
                                }
                            });
                            this.track('core_web_vitals', {
                                metric: 'CLS',
                                value: Math.round(clsScore * 1000) / 1000,
                                rating: clsScore < 0.1 ? 'good' : clsScore < 0.25 ? 'needs_improvement' : 'poor'
                            });
                        }).observe({ entryTypes: ['layout-shift'] });
                    }
                }, 1000);
            });
        },

        // Device detection
        getDeviceType: function() {
            const ua = navigator.userAgent;
            if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
                return 'tablet';
            }
            if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
                return 'mobile';
            }
            return 'desktop';
        },

        getBrowserInfo: function() {
            const ua = navigator.userAgent;
            let browser = 'Unknown';
            
            if (ua.indexOf('Firefox') > -1) browser = 'Firefox';
            else if (ua.indexOf('SamsungBrowser') > -1) browser = 'Samsung';
            else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) browser = 'Opera';
            else if (ua.indexOf('Trident') > -1) browser = 'IE';
            else if (ua.indexOf('Edge') > -1) browser = 'Edge';
            else if (ua.indexOf('Chrome') > -1) browser = 'Chrome';
            else if (ua.indexOf('Safari') > -1) browser = 'Safari';
            
            return browser;
        },

        getOS: function() {
            const ua = navigator.userAgent;
            if (ua.indexOf('Win') > -1) return 'Windows';
            if (ua.indexOf('Mac') > -1) return 'MacOS';
            if (ua.indexOf('Linux') > -1) return 'Linux';
            if (ua.indexOf('Android') > -1) return 'Android';
            if (ua.indexOf('iOS') > -1 || ua.indexOf('iPhone') > -1 || ua.indexOf('iPad') > -1) return 'iOS';
            return 'Unknown';
        },

        isReturningVisitor: function() {
            return localStorage.getItem('vm_returning') === 'true' || false;
        },

        // Send tracking event
        track: function(eventType, data = {}) {
            const payload = {
                api_key: this.config.apiKey,
                event_type: eventType,
                session_id: this.sessionData.sessionId,
                visitor_id: this.sessionData.visitorId,
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent,
                ...data
            };

            if (this.config.debug) {
                console.log('[VisionMetrics] Track:', eventType, payload);
            }

            // Store in session
            this.sessionData.events.push({ type: eventType, data, timestamp: Date.now() });

            // Send to server
            fetch(this.config.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                keepalive: true
            }).catch(err => {
                if (this.config.debug) console.error('[VisionMetrics] Error:', err);
            });

            // Mark as returning visitor
            localStorage.setItem('vm_returning', 'true');
        },

        // Identify user
        identify: function(userData) {
            this.track('identify', {
                email: userData.email,
                phone: userData.phone,
                name: userData.name,
                ...userData
            });
        },

        // Utility: Throttle
        throttle: function(func, delay) {
            let lastCall = 0;
            return function(...args) {
                const now = Date.now();
                if (now - lastCall >= delay) {
                    lastCall = now;
                    return func.apply(this, args);
                }
            };
        }
    };

    // Export to window
    window.VisionMetrics = VisionMetrics;
})();







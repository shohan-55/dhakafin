/* ==========================================================================
   DhakaFin — interactive engine
   Three.js particle globe · GSAP choreography · Lenis smooth scroll
   ========================================================================== */
import * as THREE from 'three';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger);

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarse = window.matchMedia('(hover: none), (pointer: coarse)').matches;
if (reduced) document.documentElement.classList.add('no-motion');

/* ==========================================================================
   Lenis smooth scroll
   ========================================================================== */
let lenis = null;
if (!reduced) {
    lenis = new Lenis({ lerp: 0.09, wheelMultiplier: 1.0, touchMultiplier: 1.4 });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.lagSmoothing(0);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
}
function scrollToTarget(sel) {
    const el = document.querySelector(sel);
    if (!el) return;
    if (lenis) lenis.scrollTo(el, { offset: -70, duration: 1.6, easing: (t) => 1 - Math.pow(1 - t, 4) });
    else el.scrollIntoView({ behavior: 'smooth' });
}
document.querySelectorAll('a[data-scroll]').forEach((a) => {
    a.addEventListener('click', (e) => {
        const href = a.getAttribute('href');
        if (href && href.startsWith('#')) { e.preventDefault(); scrollToTarget(href); }
    });
});

/* ==========================================================================
   Custom cursor
   ========================================================================== */
if (!coarse && !reduced) {
    const dot = document.getElementById('cursorDot');
    const ring = document.getElementById('cursorRing');
    const label = document.getElementById('cursorLabel');
    if (dot && ring) {
        const dx = gsap.quickTo(dot, 'x', { duration: 0.08, ease: 'power2.out' });
        const dy = gsap.quickTo(dot, 'y', { duration: 0.08, ease: 'power2.out' });
        const rx = gsap.quickTo(ring, 'x', { duration: 0.45, ease: 'power3.out' });
        const ry = gsap.quickTo(ring, 'y', { duration: 0.45, ease: 'power3.out' });
        window.addEventListener('mousemove', (e) => { dx(e.clientX); dy(e.clientY); rx(e.clientX); ry(e.clientY); });

        const hoverables = 'a, button, [data-tilt], input, textarea, select';
        document.addEventListener('mouseover', (e) => {
            const t = e.target.closest(hoverables);
            if (t) {
                const txt = t.getAttribute('data-cursor-text');
                if (txt) { label.textContent = txt; ring.classList.add('is-label'); }
                else ring.classList.add('is-hover');
            }
        });
        document.addEventListener('mouseout', (e) => {
            if (e.target.closest(hoverables)) ring.classList.remove('is-hover', 'is-label');
        });
    }
}

/* ==========================================================================
   Magnetic elements
   ========================================================================== */
if (!coarse && !reduced) {
    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        const strength = 0.35;
        el.addEventListener('mousemove', (e) => {
            const r = el.getBoundingClientRect();
            const mx = e.clientX - (r.left + r.width / 2);
            const my = e.clientY - (r.top + r.height / 2);
            gsap.to(el, { x: mx * strength, y: my * strength, duration: 0.6, ease: 'power3.out' });
        });
        el.addEventListener('mouseleave', () => {
            gsap.to(el, { x: 0, y: 0, duration: 0.9, ease: 'elastic.out(1, 0.35)' });
        });
    });
}

/* ==========================================================================
   3D tilt cards
   ========================================================================== */
if (!coarse && !reduced) {
    document.querySelectorAll('[data-tilt]').forEach((el) => {
        const max = 6;
        el.addEventListener('mousemove', (e) => {
            const r = el.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            gsap.to(el, {
                rotateY: px * max, rotateX: -py * max, transformPerspective: 900,
                duration: 0.6, ease: 'power2.out',
            });
        });
        el.addEventListener('mouseleave', () => gsap.to(el, { rotateX: 0, rotateY: 0, duration: 0.9, ease: 'power3.out' }));
    });
}

/* ==========================================================================
   THREE.JS — hero scene
   ========================================================================== */
(function heroScene() {
    const canvas = document.getElementById('heroCanvas');
    if (!canvas || reduced) { if (canvas) canvas.classList.add('is-live'); return; }

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x04070d, 0.035);

    const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 100);
    camera.position.set(0, 0.6, 26);

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'high-performance' });
    } catch (e) {
        canvas.classList.add('is-live');
        return;
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));

    const isSmall = window.innerWidth < 820;
    const MINT = new THREE.Color(0x2ed98f);
    const MINT2 = new THREE.Color(0x8bf3cb);
    const GOLD = new THREE.Color(0xe7c164);

    // --- particle globe (fibonacci sphere) ---
    const globe = new THREE.Group();
    const COUNT = isSmall ? 900 : 2100;
    const pos = new Float32Array(COUNT * 3);
    const col = new Float32Array(COUNT * 3);
    const R = 11.5;
    for (let i = 0; i < COUNT; i++) {
        const t = i / (COUNT - 1);
        const phi = Math.acos(1 - 2 * t);
        const theta = Math.PI * (1 + Math.sqrt(5)) * i;
        const jitter = 1 + (Math.random() - 0.5) * 0.02;
        pos[i * 3] = R * jitter * Math.sin(phi) * Math.cos(theta);
        pos[i * 3 + 1] = R * jitter * Math.cos(phi);
        pos[i * 3 + 2] = R * jitter * Math.sin(phi) * Math.sin(theta);
        // equatorial band glows mint-gold, poles mint
        const band = Math.abs(pos[i * 3 + 1]) < 3.2 && Math.random() < 0.35;
        const c = band ? GOLD : (Math.random() < 0.12 ? MINT2 : MINT);
        col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
    }
    const gGeo = new THREE.BufferGeometry();
    gGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    gGeo.setAttribute('color', new THREE.BufferAttribute(col, 3));
    const gMat = new THREE.PointsMaterial({
        size: isSmall ? 0.075 : 0.062, vertexColors: true, transparent: true, opacity: 0.85,
        blending: THREE.AdditiveBlending, depthWrite: false, sizeAttenuation: true,
    });
    globe.add(new THREE.Points(gGeo, gMat));

    // --- inner wireframe core ---
    const core = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.IcosahedronGeometry(5.6, 1)),
        new THREE.LineBasicMaterial({ color: 0xe7c164, transparent: true, opacity: 0.14 })
    );
    globe.add(core);
    const coreInner = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.OctahedronGeometry(3.4, 0)),
        new THREE.LineBasicMaterial({ color: 0x8bf3cb, transparent: true, opacity: 0.2 })
    );
    globe.add(coreInner);

    // --- gyroscope rings ---
    function ring(radius, color, opacity, tiltX, tiltZ) {
        const r = new THREE.Mesh(
            new THREE.TorusGeometry(radius, 0.022, 8, 180),
            new THREE.MeshBasicMaterial({ color, transparent: true, opacity })
        );
        r.rotation.set(tiltX, 0, tiltZ);
        return r;
    }
    const ringA = ring(13.6, 0x2ed98f, 0.28, Math.PI / 2.15, 0.18);
    const ringB = ring(15.1, 0xe7c164, 0.18, Math.PI / 1.8, -0.28);
    globe.add(ringA, ringB);

    globe.position.x = isSmall ? 0 : 8.4;
    globe.position.y = 0.4;
    scene.add(globe);

    // --- starfield ---
    const stars = isSmall ? 350 : 800;
    const sPos = new Float32Array(stars * 3);
    for (let i = 0; i < stars; i++) {
        sPos[i * 3] = (Math.random() - 0.5) * 90;
        sPos[i * 3 + 1] = (Math.random() - 0.5) * 55;
        sPos[i * 3 + 2] = (Math.random() - 0.5) * 40 - 8;
    }
    const sGeo = new THREE.BufferGeometry();
    sGeo.setAttribute('position', new THREE.BufferAttribute(sPos, 3));
    const starsMesh = new THREE.Points(sGeo, new THREE.PointsMaterial({
        size: 0.05, color: 0x93a2b4, transparent: true, opacity: 0.55,
        blending: THREE.AdditiveBlending, depthWrite: false,
    }));
    scene.add(starsMesh);

    // --- aurora ribbons (custom GLSL) ---
    const auroraUniforms = {
        uTime: { value: 0 },
        uMouse: { value: new THREE.Vector2(0, 0) },
        uColA: { value: new THREE.Color(0x2ed98f) },
        uColB: { value: new THREE.Color(0xe7c164) },
        uColC: { value: new THREE.Color(0x0a3d2e) },
    };
    const aurora = new THREE.Mesh(
        new THREE.PlaneGeometry(120, 70),
        new THREE.ShaderMaterial({
            uniforms: auroraUniforms,
            transparent: true,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
            vertexShader: /* glsl */`
                varying vec2 vUv;
                void main() {
                    vUv = uv;
                    gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
                }
            `,
            fragmentShader: /* glsl */`
                uniform float uTime;
                uniform vec2 uMouse;
                uniform vec3 uColA;
                uniform vec3 uColB;
                uniform vec3 uColC;
                varying vec2 vUv;

                float band(vec2 uv, float speed, float freq, float amp, float offset) {
                    float y = uv.y + sin(uv.x * freq + uTime * speed + offset) * amp
                            + sin(uv.x * freq * 2.7 + uTime * speed * 1.6 + offset * 2.0) * amp * 0.35;
                    float d = abs(y - 0.5 - uMouse.y * 0.08 + offset * 0.05);
                    return smoothstep(0.16, 0.0, d) * (0.55 + 0.45 * sin(uv.x * 3.0 + uTime * speed));
                }

                void main() {
                    vec2 uv = vUv;
                    uv.x += uMouse.x * 0.05;
                    float b1 = band(uv, 0.10, 2.2, 0.10, 0.0);
                    float b2 = band(uv, 0.14, 1.6, 0.13, 1.7);
                    float b3 = band(uv, 0.07, 3.1, 0.07, 3.9);
                    vec3 col = uColA * b1 * 0.30 + uColB * b2 * 0.20 + uColC * b3 * 0.45;
                    float edge = smoothstep(0.0, 0.22, uv.x) * smoothstep(1.0, 0.72, uv.x)
                               * smoothstep(0.0, 0.24, uv.y) * smoothstep(1.0, 0.62, uv.y);
                    col *= edge;
                    col *= mix(0.38, 1.0, smoothstep(0.0, 1.0, uv.y)); // dim the lower half, keep legibility
                    float hash = fract(sin(dot(vUv * uTime, vec2(12.9898, 78.233))) * 43758.5453);
                    col += (hash - 0.5) * 0.012; // dither, anti-banding
                    gl_FragColor = vec4(col, 1.0);
                }
            `,
        })
    );
    aurora.position.set(isSmall ? 0 : 6, 2, -22);
    scene.add(aurora);

    // --- interaction state ---
    let mx = 0, my = 0, tx = 0, ty = 0;
    if (!coarse) {
        window.addEventListener('mousemove', (e) => {
            tx = (e.clientX / window.innerWidth - 0.5);
            ty = (e.clientY / window.innerHeight - 0.5);
        });
    }
    let scrollProg = 0;
    ScrollTrigger.create({
        trigger: canvas.closest('section') || document.body,
        start: 'top top', end: 'bottom top', scrub: true,
        onUpdate: (self) => { scrollProg = self.progress; },
    });

    function resize() {
        const w = canvas.clientWidth || window.innerWidth;
        const h = canvas.clientHeight || window.innerHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    const clock = new THREE.Clock();
    let visible = true;
    new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; }, { threshold: 0.02 })
        .observe(canvas);

    (function tick() {
        requestAnimationFrame(tick);
        if (!visible) return;
        const t = clock.getElapsedTime();
        mx += (tx - mx) * 0.045;
        my += (ty - my) * 0.045;

        globe.rotation.y = t * 0.075 + mx * 0.55;
        globe.rotation.x = my * 0.3 + Math.sin(t * 0.11) * 0.05;
        globe.rotation.z = my * 0.08;
        core.rotation.y = -t * 0.12;
        coreInner.rotation.x = t * 0.18;
        coreInner.rotation.y = t * 0.1;
        ringA.rotation.z = t * 0.05;
        ringB.rotation.z = -t * 0.04;
        starsMesh.rotation.y = t * 0.006;

        // breathing scale + scroll pull-back
        const breathe = 1 + Math.sin(t * 0.5) * 0.012;
        const s = breathe * (1 - scrollProg * 0.18);
        globe.scale.setScalar(s);
        camera.position.z = 26 + scrollProg * 7;
        camera.position.y = 0.6 + my * 0.5 - scrollProg * 3.5;
        camera.lookAt(isSmall ? 0 : 4, 0, 0);

        auroraUniforms.uTime.value = t;
        auroraUniforms.uMouse.value.set(mx, my);
        aurora.position.x = (isSmall ? 0 : 6) + mx * 1.6;

        renderer.render(scene, camera);
    })();

    canvas.classList.add('is-live');
})();

/* ==========================================================================
   Preloader + hero intro
   ========================================================================== */
(function boot() {
    const pre = document.getElementById('preloader');
    const count = document.getElementById('preloaderCount');
    const wipe = document.getElementById('pageWipe');
    const arrived = document.documentElement.classList.contains('wipe-arrived');
    const heroBits = ['#heroEyebrow', '#heroSub', '#heroActions', '#heroStats', '.hero-side', '.hero-scroll'];

    // char-split the non-gradient hero lines (gradient stays whole — background-clip safety)
    if (!reduced) {
        document.querySelectorAll('.hero-line-inner:not(.hero-line--grad)').forEach((line) => {
            const text = line.textContent;
            line.textContent = '';
            line.setAttribute('aria-hidden', 'true');
            text.split(/\s+/).forEach((word, wi, arr) => {
                const w = document.createElement('span');
                w.className = 'hero-word';
                for (const ch of word) {
                    const c = document.createElement('span');
                    c.className = 'hero-ch';
                    c.textContent = ch;
                    w.appendChild(c);
                }
                line.appendChild(w);
                if (wi < arr.length - 1) line.appendChild(document.createTextNode(' '));
            });
        });
    }
    const charSel = '.hero-ch';
    const hasChars = !!document.querySelector(charSel);

    // set initial states
    if (hasChars) gsap.set(charSel, { yPercent: 118, rotate: 10 });
    else gsap.set('.hero-line-inner', { yPercent: 112 });
    gsap.set('.hero-line--grad', hasChars ? { yPercent: 112 } : {});
    gsap.set(heroBits, { opacity: 0, y: 26 });

    const heroIn = (tl, at) => {
        if (hasChars) {
            tl.to(charSel, { yPercent: 0, rotate: 0, duration: 1.05, ease: 'power4.out', stagger: 0.02 }, at)
                .to('.hero-line--grad', { yPercent: 0, duration: 1.1, ease: 'power4.out' }, at + 0.28);
        } else {
            tl.to('.hero-line-inner', { yPercent: 0, duration: 1.15, ease: 'power4.out', stagger: 0.11 }, at);
        }
        tl.to(heroBits, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.08 }, at + 0.4);
        return tl;
    };

    // --- arrived via page wipe: no preloader, just unveil ---
    if (arrived && wipe) {
        document.documentElement.classList.remove('wipe-arrived');
        try { sessionStorage.removeItem('df-wipe'); } catch (e) {}
        if (pre) pre.remove();
        const tl = gsap.timeline({
            onComplete: () => { gsap.set(wipe, { visibility: 'hidden' }); window.__dfWiping = false; },
        });
        tl.to('.wipe-brand', { opacity: 0, y: -18, duration: 0.32, ease: 'power2.in' }, 0.12)
            .to('.wipe-layer--a', { y: '-101%', duration: 0.72, ease: 'power4.inOut' }, 0.24)
            .to('.wipe-layer--b', { y: '-101%', duration: 0.72, ease: 'power4.inOut' }, 0.34);
        heroIn(tl, 0.5);
        return;
    }

    if (!pre || reduced) {
        if (pre) pre.remove();
        if (hasChars) gsap.set(charSel, { yPercent: 0, rotate: 0 });
        else gsap.set('.hero-line-inner', { yPercent: 0 });
        gsap.set('.hero-line--grad', { yPercent: 0 });
        gsap.set(heroBits, { opacity: 1, y: 0 });
        return;
    }
    document.body.style.overflow = 'hidden';
    const state = { v: 0 };
    const tl = gsap.timeline({
        onComplete: () => { pre.remove(); document.body.style.overflow = ''; },
    });
    tl.to(state, {
        v: 100, duration: 1.5, ease: 'power2.inOut',
        onUpdate: () => { count.textContent = String(Math.round(state.v)).padStart(2, '0'); },
    })
        .to('.preloader-inner', { opacity: 0, y: -26, duration: 0.45, ease: 'power2.in' }, '+=0.15')
        .to('.preloader-curtain--b', { clipPath: 'inset(0 0 100% 0)', duration: 0.8, ease: 'power4.inOut' }, '-=0.1')
        .to('.preloader-curtain--a', { clipPath: 'inset(0 0 100% 0)', duration: 0.8, ease: 'power4.inOut' }, '-=0.62');
    heroIn(tl, '-=0.55');
})();

/* ==========================================================================
   Scroll reveals
   ========================================================================== */
if (!reduced) {
    // generic reveals
    gsap.utils.toArray('[data-reveal]').forEach((el) => {
        gsap.fromTo(el,
            { y: 42, opacity: 0 },
            {
                y: 0, opacity: 1, duration: 1.15, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 88%', once: true },
            });
    });
    // grouped stagger reveals
    gsap.utils.toArray('[data-reveal-group]').forEach((group) => {
        gsap.fromTo(group.children,
            { y: 44, opacity: 0 },
            {
                y: 0, opacity: 1, duration: 1.05, ease: 'power3.out', stagger: 0.09,
                scrollTrigger: { trigger: group, start: 'top 86%', once: true },
            });
    });
    // headline line reveals
    gsap.utils.toArray('[data-reveal-lines]').forEach((el) => {
        const lines = el.querySelectorAll('.headline-line > span');
        if (!lines.length) return;
        gsap.fromTo(lines,
            { yPercent: 112, opacity: 1 },
            {
                yPercent: 0, duration: 1.25, ease: 'power4.out', stagger: 0.1,
                scrollTrigger: { trigger: el, start: 'top 86%', once: true },
            });
    });

    // process progress bar scrub
    const bar = document.getElementById('processBar');
    if (bar) {
        gsap.to(bar, {
            width: '100%', ease: 'none',
            scrollTrigger: { trigger: '#processTrack', start: 'top 72%', end: 'bottom 40%', scrub: 0.6 },
        });
    }

    // scroll progress
    const prog = document.getElementById('scrollProgress');
    if (prog) {
        gsap.to(prog, {
            scaleX: 1, ease: 'none',
            scrollTrigger: { trigger: document.body, start: 'top top', end: 'bottom bottom', scrub: 0.3 },
        });
    }

    // hero stat counters
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseFloat(el.getAttribute('data-count'));
        const obj = { v: 0 };
        ScrollTrigger.create({
            trigger: el, start: 'top 92%', once: true,
            onEnter: () => gsap.to(obj, {
                v: target, duration: 2.1, ease: 'power3.out',
                onUpdate: () => { el.textContent = Math.round(obj.v); },
            }),
        });
    });

    // parallax on section headers
    gsap.utils.toArray('.section-head').forEach((el) => {
        gsap.fromTo(el, { y: 30 }, {
            y: -30, ease: 'none',
            scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: 1 },
        });
    });
} else {
    document.querySelectorAll('[data-count]').forEach((el) => {
        el.textContent = el.getAttribute('data-count');
    });
}

/* ==========================================================================
   Header state + mobile menu
   ========================================================================== */
(function header() {
    const header = document.getElementById('siteHeader');
    const toggle = document.getElementById('navToggle');
    const menu = document.getElementById('mobileMenu');
    if (!header) return;

    const onScroll = () => {
        const y = lenis ? lenis.scroll : window.scrollY;
        header.classList.toggle('is-scrolled', y > 30);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    if (lenis) lenis.on('scroll', onScroll);
    onScroll();

    if (toggle && menu) {
        const links = menu.querySelectorAll('.mobile-nav-link');
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('is-open');
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            menu.setAttribute('aria-hidden', String(!open));
            if (lenis) open ? lenis.stop() : lenis.start();
            document.body.style.overflow = open ? 'hidden' : '';
            if (open) {
                gsap.set(links, { opacity: 0, y: 28 });
                gsap.to(links, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out', stagger: 0.07, delay: 0.25 });
            }
        });
        links.forEach((l) => l.addEventListener('click', () => {
            menu.classList.remove('is-open');
            toggle.classList.remove('is-open');
            document.body.style.overflow = '';
            if (lenis) lenis.start();
        }));
    }
})();

/* ==========================================================================
   Footer word letter stagger on view
   ========================================================================== */
if (!reduced) {
    const fw = document.querySelector('.footer-word');
    if (fw) {
        gsap.from(fw.querySelectorAll('span'), {
            yPercent: 60, opacity: 0, duration: 1.1, ease: 'power4.out', stagger: 0.05,
            scrollTrigger: { trigger: fw, start: 'top 92%', once: true },
        });
    }
}

/* ==========================================================================
   Page transitions — curtain wipe between pages
   ========================================================================== */
if (!reduced) {
    document.addEventListener('click', (e) => {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target.closest('a[href]');
        if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-scroll') || a.hasAttribute('data-no-wipe')) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#')) return;
        let url;
        try { url = new URL(href, window.location.href); } catch (err) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.hash) return;
        if (window.__dfWiping) { e.preventDefault(); return; }

        const wipe = document.getElementById('pageWipe');
        if (!wipe) return; // let normal navigation happen
        e.preventDefault();
        window.__dfWiping = true;
        try { sessionStorage.setItem('df-wipe', '1'); } catch (err) {}
        const go = () => { window.location.href = url.href; };
        setTimeout(go, 1600); // hard fallback — never trap the user
        gsap.set(wipe, { visibility: 'visible' });
        gsap.timeline()
            .to('.wipe-layer--b', { y: '0%', duration: 0.55, ease: 'power4.inOut' }, 0)
            .to('.wipe-layer--a', { y: '0%', duration: 0.55, ease: 'power4.inOut' }, 0.09)
            .to('.wipe-brand', { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' }, 0.42)
            .call(go, null, 0.72);
    }, true);
}

/* ==========================================================================
   Nav link text scramble on hover
   ========================================================================== */
if (!coarse && !reduced) {
    const glyphs = '!<>-_/[]{}=+*^?#%';
    document.querySelectorAll('.nav-link').forEach((link) => {
        const original = link.textContent;
        let iv = null;
        link.addEventListener('mouseenter', () => {
            let frame = 0;
            clearInterval(iv);
            iv = setInterval(() => {
                frame++;
                const prog = frame / 9;
                link.textContent = original.split('').map((c, i) =>
                    (i / original.length < prog) ? c : glyphs[(Math.random() * glyphs.length) | 0]
                ).join('');
                if (frame >= 9) { clearInterval(iv); link.textContent = original; }
            }, 34);
        });
        link.addEventListener('mouseleave', () => { clearInterval(iv); link.textContent = original; });
    });
}

/* ==========================================================================
   Card spotlight — cursor-following glow
   ========================================================================== */
if (!reduced) {
    document.addEventListener('pointermove', (e) => {
        const t = e.target.closest && e.target.closest('[data-spotlight]');
        if (!t) return;
        const r = t.getBoundingClientRect();
        t.style.setProperty('--mx', `${e.clientX - r.left}px`);
        t.style.setProperty('--my', `${e.clientY - r.top}px`);
    }, { passive: true });
}

/* boot flag for the failsafe watchdog */
window.__dfBooted = true;

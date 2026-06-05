/* ==========================================================================
   CADENZA MUSIC ACADEMY - THREE.JS INTERACTIVE 3D VIOLIN STRINGS
   ========================================================================== */

let scene, camera, renderer;
let strings = [];
let raycaster, mouse;
let hoveredString = null;
let noteParticles = [];
let mouseParticles = [];
let cursor3D = new THREE.Vector3(0, 0, 0);

// Web Audio API Context
let audioCtx = null;

// Violin String Tunings & Colors
const VIOLIN_STRINGS = [
    { id: 'string_G', note: 'G3', freq: 196.00, color: 0x9d4edd, label: 'Sol (G3)' }, // Purple
    { id: 'string_D', note: 'D4', freq: 293.66, color: 0x00f5d4, label: 'Re (D4)' },  // Teal
    { id: 'string_A', note: 'A4', freq: 440.00, color: 0xff007f, label: 'La (A4)' },  // Neon Pink
    { id: 'string_E', note: 'E5', freq: 659.25, color: 0xffa500, label: 'Mi (E5)' }   // Orange
];

// Initialize the 3D Scene
function init3DViolin() {
    const container = document.getElementById('violin3dCanvas').parentElement;
    if (!container) return;

    // Create Scene
    scene = new THREE.Scene();

    // Create Camera (looking down at the vertical strings)
    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
    camera.position.set(0, 0, 8);
    camera.lookAt(0, 0, 0);

    // Create Renderer
    renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('violin3dCanvas'), antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
    dirLight.position.set(2, 5, 5);
    scene.add(dirLight);

    // Build the Strings & Fingerboard
    buildFingerboard();

    // Raycasting & Mouse Tracking
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // Event Listeners
    window.addEventListener('resize', onWindowResize);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mousedown', onMouseDown);
    window.addEventListener('scroll', onScrollTransition);

    // Start Loop
    animate();
}

// Build fingerboard backdrop and the 4 strings
function buildFingerboard() {
    // 1. Add an abstract dark wood fingerboard backboard
    const fbGeo = new THREE.BoxGeometry(3.6, 6.5, 0.15);
    const fbMat = new THREE.MeshStandardMaterial({
        color: 0x0c0914,
        roughness: 0.8,
        metalness: 0.1
    });
    const fingerboard = new THREE.Mesh(fbGeo, fbMat);
    fingerboard.position.set(0, 0, -0.2);
    scene.add(fingerboard);

    // 2. Add bridge highlights at top and bottom
    const bridgeGeo = new THREE.BoxGeometry(3.6, 0.15, 0.3);
    const bridgeMat = new THREE.MeshStandardMaterial({ color: 0x1a1528, roughness: 0.5 });
    
    const topBridge = new THREE.Mesh(bridgeGeo, bridgeMat);
    topBridge.position.set(0, 2.8, 0);
    scene.add(topBridge);

    const bottomBridge = new THREE.Mesh(bridgeGeo, bridgeMat);
    bottomBridge.position.set(0, -2.8, 0);
    scene.add(bottomBridge);

    // 3. Create the 4 Strings
    const stringSpacing = 0.8;
    const stringLength = 5.6;

    VIOLIN_STRINGS.forEach((strData, index) => {
        // Position spaced horizontally
        const xPos = (index - 1.5) * stringSpacing;

        // Thin cylinder geometry representing the string
        const strGeo = new THREE.CylinderGeometry(0.025, 0.025, stringLength, 8);
        const strMat = new THREE.MeshStandardMaterial({
            color: strData.color,
            roughness: 0.1,
            metalness: 0.9,
            emissive: strData.color,
            emissiveIntensity: 0.3
        });

        const stringMesh = new THREE.Mesh(strGeo, strMat);
        stringMesh.position.set(xPos, 0, 0.1);
        stringMesh.castShadow = true;

        // Metadata for physical vibration and audio
        stringMesh.userData = {
            id: strData.id,
            note: strData.note,
            freq: strData.freq,
            color: strData.color,
            originalX: xPos,
            vibrating: false,
            vibrationTime: 0,
            vibrationAmplitude: 0
        };

        scene.add(stringMesh);
        strings.push(stringMesh);
    });
}

// Mouse Position Tracking
function onMouseMove(event) {
    const container = document.getElementById('violin3dCanvas').parentElement;
    const rect = renderer.domElement.getBoundingClientRect();
    
    mouse.x = ((event.clientX - rect.left) / container.clientWidth) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / container.clientHeight) * 2 + 1;

    // Track 3D cursor position for particle effects
    const tempV = new THREE.Vector3(mouse.x, mouse.y, 0.5);
    tempV.unproject(camera);
    const dir = tempV.sub(camera.position).normalize();
    const distance = -camera.position.z / dir.z; // intersect with plane z=0
    cursor3D.copy(camera.position).add(dir.multiplyScalar(distance));

    // Spawn mouse follow trail
    if (Math.random() < 0.3) {
        spawnMouseParticle(cursor3D.x, cursor3D.y, 0.2);
    }
}

// Handle Mouse Click or bow gesture
function onMouseDown(event) {
    if (!raycaster || !mouse) return;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(strings);

    if (intersects.length > 0) {
        const clickedString = intersects[0].object;
        playViolinString(clickedString);
    }
}

// Play sound, vibrate string, spawn particles
function playViolinString(stringMesh) {
    // Vibrate string physics
    stringMesh.userData.vibrating = true;
    stringMesh.userData.vibrationTime = 0;
    stringMesh.userData.vibrationAmplitude = 0.15; // Shaking amplitude

    // Pulse emissive glow
    gsap.to(stringMesh.material, {
        emissiveIntensity: 1.5,
        duration: 0.1,
        yoyo: true,
        repeat: 1,
        onComplete: () => {
            stringMesh.material.emissiveIntensity = 0.3;
        }
    });

    // Initialize audio context
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }

    // Play synthesized violin tone
    synthesizeViolin(stringMesh.userData.freq);

    // Spawn particles along the string length
    spawnNoteParticles(stringMesh.position.x, cursor3D.y, stringMesh.position.z, stringMesh.userData.color);
}

// Web Audio API Violin Synthesizer (Sawtooth, Lowpass Filter, LFO Vibrato, Bow Envelope)
function synthesizeViolin(frequency) {
    if (!audioCtx) return;

    const osc = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();
    const filterNode = audioCtx.createBiquadFilter();

    // 1. Set Sawtooth Wave (rich string harmonics)
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(frequency, audioCtx.currentTime);

    // 2. Lowpass Filter to warm up the tone and make it woody
    filterNode.type = 'lowpass';
    filterNode.frequency.setValueAtTime(1200, audioCtx.currentTime); // cutoff frequency
    filterNode.Q.setValueAtTime(1, audioCtx.currentTime);

    // 3. LFO (Vibrato) - modulates frequency to mimic violinist finger movement
    const lfo = audioCtx.createOscillator();
    const lfoGain = audioCtx.createGain();
    lfo.type = 'sine';
    lfo.frequency.setValueAtTime(6, audioCtx.currentTime); // 6 Hz vibrato
    lfoGain.gain.setValueAtTime(4, audioCtx.currentTime);  // frequency swing

    lfo.connect(lfoGain);
    lfoGain.connect(osc.frequency);

    // 4. Bow Attack / Release Envelope (ADSR)
    const now = audioCtx.currentTime;
    gainNode.gain.setValueAtTime(0, now);
    gainNode.gain.linearRampToValueAtTime(0.4, now + 0.12); // Attack (slow bow contact)
    gainNode.gain.exponentialRampToValueAtTime(0.2, now + 0.3); // Decay to Sustain
    gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 1.2); // Release (bow lift)

    // Connect Nodes
    osc.connect(filterNode);
    filterNode.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    // Start oscillators
    lfo.start(now);
    osc.start(now);

    // Stop after duration
    lfo.stop(now + 1.2);
    osc.stop(now + 1.2);
}

// Spawn particles when string is played
function spawnNoteParticles(x, y, z, color) {
    const particleCount = 6;
    for (let i = 0; i < particleCount; i++) {
        const geo = new THREE.SphereGeometry(0.08, 8, 8);
        const mat = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: 0.9
        });
        
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(x, y + (Math.random() - 0.5) * 1.5, z);
        scene.add(mesh);

        noteParticles.push({
            mesh: mesh,
            vx: (Math.random() - 0.5) * 0.05,
            vy: (Math.random() - 0.5) * 0.05,
            vz: 0.02 + Math.random() * 0.04,
            life: 1.0,
            decay: 0.02 + Math.random() * 0.01
        });
    }
}

// Spawn mouse follow trail
function spawnMouseParticle(x, y, z) {
    const geo = new THREE.BoxGeometry(0.05, 0.05, 0.05);
    const mat = new THREE.MeshBasicMaterial({
        color: 0x00f5d4,
        transparent: true,
        opacity: 0.5
    });
    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.set(x, y, z);
    scene.add(mesh);

    mouseParticles.push({
        mesh: mesh,
        vx: (Math.random() - 0.5) * 0.01,
        vy: (Math.random() - 0.5) * 0.01,
        vz: 0.0,
        life: 1.0,
        decay: 0.04
    });
}

// Window resize handler
function onWindowResize() {
    const container = document.getElementById('violin3dCanvas').parentElement;
    if (!container || !camera || !renderer) return;

    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

// Camera parallax transition based on scrolling
function onScrollTransition() {
    const scrollY = window.scrollY;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = scrollY / (maxScroll || 1);

    if (camera) {
        // Linearly interpolate camera position based on scroll position
        // Zooming in and tilting as we scroll down the landing page
        camera.position.x = THREE.MathUtils.lerp(0, 1.8, scrollPercent);
        camera.position.y = THREE.MathUtils.lerp(0, 0.5, scrollPercent);
        camera.position.z = THREE.MathUtils.lerp(8, 6.2, scrollPercent);
        camera.lookAt(new THREE.Vector3(0, 0, 0));
    }
}

// Main render frame loop
function animate() {
    requestAnimationFrame(animate);

    // Raycasting hover state detection
    if (raycaster && mouse && strings.length > 0) {
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(strings);

        if (intersects.length > 0) {
            const currentObj = intersects[0].object;
            if (hoveredString !== currentObj) {
                hoveredString = currentObj;
                // Automatically play string when hovered (like drawing a bow across it!)
                playViolinString(currentObj);
            }
        } else {
            hoveredString = null;
        }
    }

    // Animate String Physical Vibrations (Shaking/Vibrating)
    strings.forEach(str => {
        if (str.userData.vibrating) {
            str.userData.vibrationTime += 0.45;
            // Apply quick physical decay displacement on X coordinate
            const offset = Math.sin(str.userData.vibrationTime) * str.userData.vibrationAmplitude;
            str.position.x = str.userData.originalX + offset;
            
            // Decelerate vibration
            str.userData.vibrationAmplitude *= 0.92;
            if (str.userData.vibrationAmplitude < 0.002) {
                str.userData.vibrating = false;
                str.position.x = str.userData.originalX;
            }
        }
    });

    // Animate Particles
    for (let i = noteParticles.length - 1; i >= 0; i--) {
        const p = noteParticles[i];
        p.mesh.position.x += p.vx;
        p.mesh.position.y += p.vy;
        p.mesh.position.z += p.vz;
        p.life -= p.decay;
        p.mesh.material.opacity = p.life;
        p.mesh.scale.setScalar(p.life);

        if (p.life <= 0) {
            scene.remove(p.mesh);
            p.mesh.geometry.dispose();
            p.mesh.material.dispose();
            noteParticles.splice(i, 1);
        }
    }

    // Animate Mouse Trail
    for (let i = mouseParticles.length - 1; i >= 0; i--) {
        const p = mouseParticles[i];
        p.mesh.position.x += p.vx;
        p.mesh.position.y += p.vy;
        p.mesh.position.z += p.vz;
        p.life -= p.decay;
        p.mesh.material.opacity = p.life;

        if (p.life <= 0) {
            scene.remove(p.mesh);
            p.mesh.geometry.dispose();
            p.mesh.material.dispose();
            mouseParticles.splice(i, 1);
        }
    }

    if (renderer && scene && camera) {
        renderer.render(scene, camera);
    }
}

// Document Ready trigger
$(document).ready(function() {
    if (document.getElementById('violin3dCanvas')) {
        init3DViolin();
    }
});

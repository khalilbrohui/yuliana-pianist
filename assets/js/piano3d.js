/* ==========================================================================
   CADENZA MUSIC ACADEMY - THREE.JS INTERACTIVE 3D PIANO
   ========================================================================== */

let scene, camera, renderer;
let whiteKeys = [], blackKeys = [], allKeys = [];
let raycaster, mouse;
let hoveredKey = null;
let noteParticles = [];
let mouseParticles = [];
let cursor3D = new THREE.Vector3(0, 0, 0);

// Web Audio API Context
let audioCtx = null;

// Frequencies for Piano Octave C4 to C5
const NOTE_FREQS = {
    // White Keys
    'w_0': { note: 'C4', freq: 261.63 },
    'w_1': { note: 'D4', freq: 293.66 },
    'w_2': { note: 'E4', freq: 329.63 },
    'w_3': { note: 'F4', freq: 349.23 },
    'w_4': { note: 'G4', freq: 392.00 },
    'w_5': { note: 'A4', freq: 440.00 },
    'w_6': { note: 'B4', freq: 493.88 },
    'w_7': { note: 'C5', freq: 523.25 },
    // Black Keys
    'b_0': { note: 'C#4', freq: 277.18 },
    'b_1': { note: 'D#4', freq: 311.13 },
    'b_2': { note: 'F#4', freq: 369.99 },
    'b_3': { note: 'G#4', freq: 415.30 },
    'b_4': { note: 'A#4', freq: 466.16 }
};

// Initialize the 3D Scene
function init3DPiano() {
    const container = document.getElementById('piano3dCanvas').parentElement;
    if (!container) return;

    // Create Scene
    scene = new THREE.Scene();

    // Create Camera
    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
    // Initial Camera position (top-down, looking down at piano)
    camera.position.set(0, 8, 8);
    camera.lookAt(0, 0, 0);

    // Create Renderer
    renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('piano3dCanvas'), antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
    dirLight.position.set(5, 15, 5);
    dirLight.castShadow = true;
    scene.add(dirLight);

    // Spotlights for key glow highlights
    const spotLight = new THREE.SpotLight(0x9d4edd, 2);
    spotLight.position.set(0, 10, 0);
    spotLight.angle = Math.PI / 4;
    spotLight.penumbra = 0.5;
    spotLight.castShadow = true;
    scene.add(spotLight);

    const spotLightTeal = new THREE.SpotLight(0x00f5d4, 1.5);
    spotLightTeal.position.set(5, 8, 2);
    spotLightTeal.angle = Math.PI / 4;
    spotLightTeal.penumbra = 0.5;
    scene.add(spotLightTeal);

    // Build the Keyboard
    buildKeyboard();

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

// Build white and black piano keys
function buildKeyboard() {
    const whiteKeyCount = 8;
    const wWidth = 0.95;
    const wHeight = 0.35;
    const wDepth = 4.0;
    const spacing = 0.04;

    const wGeo = new THREE.BoxGeometry(wWidth, wHeight, wDepth);
    
    // Create White Keys
    for (let i = 0; i < whiteKeyCount; i++) {
        // Material with subtle gloss
        const wMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            roughness: 0.1,
            metalness: 0.1,
            emissive: 0x111111
        });
        
        const keyMesh = new THREE.Mesh(wGeo, wMat);
        // Position keys centered around origin x=0
        const xPos = (i - (whiteKeyCount - 1) / 2) * (wWidth + spacing);
        keyMesh.position.set(xPos, 0, 0);
        keyMesh.castShadow = true;
        keyMesh.receiveShadow = true;
        
        // Add meta info
        keyMesh.userData = {
            isBlack: false,
            id: `w_${i}`,
            originalY: 0,
            originalColor: 0xffffff,
            pressed: false
        };

        scene.add(keyMesh);
        whiteKeys.push(keyMesh);
        allKeys.push(keyMesh);
    }

    // Create Black Keys
    const bWidth = 0.55;
    const bHeight = 0.45;
    const bDepth = 2.4;
    const blackIndices = [0, 1, 3, 4, 5]; // Indices of white keys after which a black key sits

    const bGeo = new THREE.BoxGeometry(bWidth, bHeight, bDepth);

    for (let i = 0; i < blackIndices.length; i++) {
        const bMat = new THREE.MeshStandardMaterial({
            color: 0x1a1a24,
            roughness: 0.1,
            metalness: 0.3,
            emissive: 0x050505
        });

        const keyMesh = new THREE.Mesh(bGeo, bMat);
        
        // Find x coordinate between white keys
        const leftWhiteKeyX = whiteKeys[blackIndices[i]].position.x;
        const rightWhiteKeyX = whiteKeys[blackIndices[i] + 1].position.x;
        const xPos = (leftWhiteKeyX + rightWhiteKeyX) / 2;

        // Black keys are elevated and recessed
        keyMesh.position.set(xPos, wHeight / 2 + bHeight / 2 - 0.05, -bDepth / 4);
        keyMesh.castShadow = true;
        keyMesh.receiveShadow = true;

        keyMesh.userData = {
            isBlack: true,
            id: `b_${i}`,
            originalY: keyMesh.position.y,
            originalColor: 0x1a1a24,
            pressed: false
        };

        scene.add(keyMesh);
        blackKeys.push(keyMesh);
        allKeys.push(keyMesh);
    }
}

// Mouse Position Tracking & Hover Highlight
function onMouseMove(event) {
    // Raycaster mouse coords
    const container = document.getElementById('piano3dCanvas').parentElement;
    const rect = renderer.domElement.getBoundingClientRect();
    
    mouse.x = ((event.clientX - rect.left) / container.clientWidth) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / container.clientHeight) * 2 + 1;

    // Track 3D cursor position for cursor particle effect
    const tempV = new THREE.Vector3(mouse.x, mouse.y, 0.5);
    tempV.unproject(camera);
    const dir = tempV.sub(camera.position).normalize();
    const distance = -camera.position.y / dir.y; // intersect with plane y=0
    cursor3D.copy(camera.position).add(dir.multiplyScalar(distance));

    // Spawn mouse follow dust particles
    if (Math.random() < 0.4) {
        spawnMouseParticle(cursor3D.x, cursor3D.y, cursor3D.z);
    }
}

// Handle Mouse Click Interaction
function onMouseDown(event) {
    if (!raycaster || !mouse) return;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(allKeys);

    if (intersects.length > 0) {
        const clickedKey = intersects[0].object;
        playPianoNote(clickedKey);
    }
}

// Play synthesizer sound and animate key
function playPianoNote(keyMesh) {
    if (keyMesh.userData.pressed) return;
    
    keyMesh.userData.pressed = true;

    // Initialize Web Audio context on user action
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }

    const keyId = keyMesh.userData.id;
    const noteData = NOTE_FREQS[keyId];

    if (noteData) {
        // Trigger Synth Sound
        triggerSynth(noteData.freq);

        // Spawn Floating Particle Notes
        spawnNoteParticles(keyMesh.position.x, keyMesh.position.y + 0.5, keyMesh.position.z);
    }

    // Highlight key visually on press
    const targetY = keyMesh.userData.originalY - 0.12;
    const keyColor = keyMesh.userData.isBlack ? 0x00f5d4 : 0x9d4edd;
    keyMesh.material.emissive.setHex(keyColor);

    // Animate key dip
    gsap.to(keyMesh.position, {
        y: targetY,
        duration: 0.1,
        yoyo: true,
        repeat: 1,
        ease: 'power1.inOut',
        onComplete: () => {
            keyMesh.userData.pressed = false;
            // Restore original material emissive color
            keyMesh.material.emissive.setHex(keyMesh.userData.isBlack ? 0x050505 : 0x111111);
        }
    });
}

// Web Audio API Synthesizer note player (ASDR Envelope)
function triggerSynth(frequency) {
    if (!audioCtx) return;

    // Create nodes
    const osc = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();

    osc.type = 'triangle'; // Warm retro sound
    osc.frequency.setValueAtTime(frequency, audioCtx.currentTime);

    // ADSR Envelope
    const now = audioCtx.currentTime;
    gainNode.gain.setValueAtTime(0, now);
    gainNode.gain.linearRampToValueAtTime(0.35, now + 0.05); // Attack
    gainNode.gain.exponentialRampToValueAtTime(0.15, now + 0.3); // Decay & Sustain level
    gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 1.2); // Release

    osc.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    osc.start(now);
    osc.stop(now + 1.2);
}

// Spawn Floating Musical Notes when key is pressed
function spawnNoteParticles(x, y, z) {
    const colors = [0x9d4edd, 0x00f5d4, 0xff007f, 0xffffff];
    const particleCount = 4;

    for (let i = 0; i < particleCount; i++) {
        // Create a small sphere that mimics a note head
        const geo = new THREE.SphereGeometry(0.12, 8, 8);
        const mat = new THREE.MeshBasicMaterial({
            color: colors[Math.floor(Math.random() * colors.length)],
            transparent: true,
            opacity: 0.9
        });
        
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(x + (Math.random() - 0.5) * 0.4, y, z + (Math.random() - 0.5) * 0.4);
        scene.add(mesh);

        noteParticles.push({
            mesh: mesh,
            vx: (Math.random() - 0.5) * 0.03,
            vy: 0.03 + Math.random() * 0.04,
            vz: -0.01 - Math.random() * 0.03,
            life: 1.0, // scale from 1 to 0
            decay: 0.015 + Math.random() * 0.01
        });
    }
}

// Spawn Mouse Trail Particles
function spawnMouseParticle(x, y, z) {
    const geo = new THREE.BoxGeometry(0.06, 0.06, 0.06);
    const mat = new THREE.MeshBasicMaterial({
        color: 0x9d4edd,
        transparent: true,
        opacity: 0.6
    });
    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.set(x, y, z);
    scene.add(mesh);

    mouseParticles.push({
        mesh: mesh,
        vx: (Math.random() - 0.5) * 0.01,
        vy: (Math.random() - 0.5) * 0.01,
        vz: (Math.random() - 0.5) * 0.01,
        life: 1.0,
        decay: 0.03
    });
}

// Window resize handler
function onWindowResize() {
    const container = document.getElementById('piano3dCanvas').parentElement;
    if (!container || !camera || !renderer) return;

    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

// Parallax 3D transitions based on window scroll
function onScrollTransition() {
    const scrollY = window.scrollY;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = scrollY / (maxScroll || 1);

    if (camera) {
        // Linearly interpolate camera position and target based on scroll position
        // From (0, 8, 8) in landing, to a side view (4, 4, 6), tilting rotation
        camera.position.x = THREE.MathUtils.lerp(0, 4.5, scrollPercent);
        camera.position.y = THREE.MathUtils.lerp(8, 3.5, scrollPercent);
        camera.position.z = THREE.MathUtils.lerp(8, 5.5, scrollPercent);
        camera.lookAt(new THREE.Vector3(0, 0.2, 0));
    }
}

// Main render frame loop
function animate() {
    requestAnimationFrame(animate);

    // Raycaster detection for hover state
    if (raycaster && mouse && allKeys.length > 0) {
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(allKeys);

        if (intersects.length > 0) {
            const currentObj = intersects[0].object;
            if (hoveredKey !== currentObj) {
                if (hoveredKey) {
                    hoveredKey.material.emissive.setHex(hoveredKey.userData.isBlack ? 0x050505 : 0x111111);
                }
                hoveredKey = currentObj;
                // Light glow on hover
                const hoverGlow = currentObj.userData.isBlack ? 0x00f5d4 : 0x9d4edd;
                currentObj.material.emissive.setHex(hoverGlow);
            }
        } else {
            if (hoveredKey) {
                hoveredKey.material.emissive.setHex(hoveredKey.userData.isBlack ? 0x050505 : 0x111111);
                hoveredKey = null;
            }
        }
    }

    // Animate Key Particles (floating notes)
    for (let i = noteParticles.length - 1; i >= 0; i--) {
        const p = noteParticles[i];
        p.mesh.position.x += p.vx;
        p.mesh.position.y += p.vy;
        p.mesh.position.z += p.vz;
        p.life -= p.decay;
        p.mesh.material.opacity = p.life;
        p.mesh.scale.setScalar(p.life);

        // Simple floating wobble
        p.mesh.position.x += Math.sin(p.mesh.position.y * 3) * 0.01;

        if (p.life <= 0) {
            scene.remove(p.mesh);
            p.mesh.geometry.dispose();
            p.mesh.material.dispose();
            noteParticles.splice(i, 1);
        }
    }

    // Animate Mouse Trail Particles
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

// Document Ready auto-trigger
$(document).ready(function() {
    if (document.getElementById('piano3dCanvas')) {
        init3DPiano();
    }
});

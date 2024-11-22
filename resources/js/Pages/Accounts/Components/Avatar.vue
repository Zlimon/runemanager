<script setup>
import {ref, onMounted} from "vue";
import * as THREE from 'three';
import {OBJLoader} from 'three/examples/jsm/loaders/OBJLoader';
import {MTLLoader} from 'three/examples/jsm/loaders/MTLLoader';
import {OrbitControls} from 'three/examples/jsm/controls/OrbitControls';

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

const sceneContainer = ref(null);

onMounted(() => {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, sceneContainer.value.clientWidth / sceneContainer.value.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer();
    renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);
    sceneContainer.value.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 1);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 1.5);
    directionalLight.position.set(2, 1, 1).normalize();
    scene.add(directionalLight);

    const mtlLoader = new MTLLoader();

    const player = 'Player Annihilation 2024-09-01_12-03-23';

    mtlLoader.load('/models/' + player + '.mtl', (materials) => {
        materials.preload();

        const objLoader = new OBJLoader();
        objLoader.setMaterials(materials);
        objLoader.load('/models/' + player + '.obj', (object) => {
            object.position.set(25, -100, 25);
            object.scale.set(1, 1, 1);
            scene.add(object);
            animate();
        });
    });

    camera.position.set(100, 50, 150);
    camera.lookAt(0, 0, 0);

    // Orbit Controls for interactivity
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.update();

    const animate = () => {
        requestAnimationFrame(animate);
        // Enable transparency in the renderer
        if (sceneContainer.value !== null) {
            renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);

            // Set background to transparent
            renderer.setClearColor(0x000000, 0); // The second parameter controls the opacity, set to 0 for transparency

            renderer.render(scene, camera);
            controls.update(); // Needed to keep controls in sync
        }
    };
});
</script>

<template>
    <div ref="sceneContainer"
         class="h-64 rounded-lg border bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800"/>
</template>

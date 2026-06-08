<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import * as THREE from "three";
import { OBJLoader } from "three/examples/jsm/loaders/OBJLoader";
import { MTLLoader } from "three/examples/jsm/loaders/MTLLoader";
import { OrbitControls } from "three/examples/jsm/controls/OrbitControls";
import Loader from "@/Components/Loader.vue";

/*
 * Player 3D avatar viewer. The model is exported in-game with the Model-Dumper
 * RuneLite plugin and pushed by the RuneManager plugin to /api/plugin/avatar;
 * the backend serves it as { obj_url, mtl_url }. When no model has been pushed
 * the prop is null and we show an empty-state prompt instead.
 */
const props = defineProps({
    avatar: {
        type: Object,
        default: null,
    },
});

const sceneContainer = ref(null);
const status = ref("idle"); // 'idle' | 'loading' | 'ready' | 'error'

// Three.js handles kept outside reactivity — they must be torn down on unmount
// so navigating between accounts doesn't leak GL contexts or animation loops.
let renderer = null;
let scene = null;
let camera = null;
let controls = null;
let frameId = null;
let resizeObserver = null;

const disposeScene = () => {
    if (frameId !== null) {
        cancelAnimationFrame(frameId);
        frameId = null;
    }
    if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
    }
    if (controls) {
        controls.dispose();
        controls = null;
    }
    if (scene) {
        scene.traverse((obj) => {
            obj.geometry?.dispose();
            const materials = Array.isArray(obj.material) ? obj.material : [obj.material];
            materials.forEach((material) => {
                if (!material) {
                    return;
                }
                Object.values(material).forEach((value) => value?.isTexture && value.dispose());
                material.dispose();
            });
        });
        scene = null;
    }
    if (renderer) {
        renderer.dispose();
        renderer.domElement?.remove();
        renderer = null;
    }
};

// Recentre the loaded model at the origin and pull the camera back far enough
// to frame it, regardless of the model's native scale/position (game exports
// sit in world coordinates, not around 0,0,0).
const frameModel = (object) => {
    const box = new THREE.Box3().setFromObject(object);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());

    // Recenter the geometry at the origin (rather than offsetting the object)
    // so the facing rotation pivots around the model itself.
    object.traverse((child) => {
        if (child.isMesh && child.geometry) {
            child.geometry.translate(-center.x, -center.y, -center.z);
        }
    });

    // OSRS exports the player facing away from the default camera — spin 180°
    // about the vertical axis so the avatar looks back at the viewer.
    object.rotation.y = Math.PI;

    const maxDimension = Math.max(size.x, size.y, size.z) || 1;
    // Lower multiplier = camera sits closer = avatar fills more of the card.
    const distance = (maxDimension / 2) / Math.tan((camera.fov * Math.PI) / 360) * 1.25;

    camera.position.set(0, 0, distance);
    camera.near = distance / 100;
    camera.far = distance * 100;
    camera.updateProjectionMatrix();
    camera.lookAt(0, 0, 0);

    controls.target.set(0, 0, 0);
    controls.update();
};

const animate = () => {
    frameId = requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
};

const initViewer = () => {
    const el = sceneContainer.value;
    if (!el || !props.avatar) {
        return;
    }

    status.value = "loading";

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(60, el.clientWidth / el.clientHeight, 0.1, 1000);
    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(el.clientWidth, el.clientHeight);
    renderer.setClearColor(0x000000, 0);
    el.appendChild(renderer.domElement);

    scene.add(new THREE.AmbientLight(0xffffff, 1.2));
    const directional = new THREE.DirectionalLight(0xffffff, 1.5);
    directional.position.set(2, 3, 2).normalize();
    scene.add(directional);

    controls = new OrbitControls(camera, renderer.domElement);

    const onLoad = (object) => {
        // Our exported OBJ carries no vertex normals (Phong renders black
        // without them) and the Y-flip inverts winding, so compute normals and
        // render both faces.
        object.traverse((child) => {
            if (!child.isMesh) {
                return;
            }
            child.geometry?.computeVertexNormals();
            const materials = Array.isArray(child.material) ? child.material : [child.material];
            materials.forEach((material) => material && (material.side = THREE.DoubleSide));
        });

        scene.add(object);
        frameModel(object);
        status.value = "ready";
        animate();
    };
    const onError = () => {
        status.value = "error";
        disposeScene();
    };

    const objLoader = new OBJLoader();

    if (props.avatar.mtl_url) {
        new MTLLoader().load(
            props.avatar.mtl_url,
            (materials) => {
                materials.preload();
                objLoader.setMaterials(materials);
                objLoader.load(props.avatar.obj_url, onLoad, undefined, onError);
            },
            undefined,
            onError,
        );
    } else {
        objLoader.load(props.avatar.obj_url, onLoad, undefined, onError);
    }

    resizeObserver = new ResizeObserver(() => {
        if (!renderer || !el.clientWidth) {
            return;
        }
        camera.aspect = el.clientWidth / el.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(el.clientWidth, el.clientHeight);
    });
    resizeObserver.observe(el);
};

onMounted(() => {
    if (props.avatar) {
        initViewer();
    }
});

// A live avatar push (equipment change) bumps obj_url's cache-buster — tear the
// scene down and reload so the new model appears without a page refresh.
watch(
    () => props.avatar?.obj_url,
    (url, previous) => {
        if (url === previous) {
            return;
        }
        disposeScene();
        status.value = "idle";
        if (props.avatar) {
            initViewer();
        }
    },
);

onBeforeUnmount(disposeScene);
</script>

<template>
    <!-- Same textured frame as the Quest/Bank cards: resource-pack-dialog paints
         the pack background + border when a pack is active, otherwise it falls
         back to the bg-base-200 + border. The WebGL canvas clears transparent so
         the texture shows through behind the model. -->
    <div class="relative h-64 bg-base-200 border border-base-300 rounded resource-pack-dialog p-2">
        <!-- Canvas host: visible once we have a model and it hasn't errored. -->
        <div v-show="avatar && status !== 'error'" ref="sceneContainer" class="h-full w-full" />

        <div v-if="!avatar"
             class="flex h-full flex-col items-center justify-center gap-1 p-3 text-center">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">No avatar yet</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                Export your character with the Model-Dumper RuneLite plugin to see it here.
            </p>
        </div>

        <div v-else-if="status === 'loading'" class="absolute inset-0 flex items-center justify-center">
            <Loader :component="true" :loading="true" />
        </div>

        <div v-else-if="status === 'error'"
             class="flex h-full items-center justify-center p-3 text-center">
            <p class="text-xs text-gray-600 dark:text-gray-400">Couldn't load this avatar model.</p>
        </div>
    </div>
</template>

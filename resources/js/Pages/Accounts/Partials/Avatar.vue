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
    // Taller, full-column viewer — used for the combat tableau (player + NPC).
    expanded: {
        type: Boolean,
        default: false,
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
// Our exported OBJ carries no vertex normals (Phong renders black without them)
// and the Y-flip inverts winding, so compute normals and render both faces.
const prepareMeshes = (object) => {
    object.traverse((child) => {
        if (!child.isMesh) {
            return;
        }
        child.geometry?.computeVertexNormals();
        const materials = Array.isArray(child.material) ? child.material : [child.material];
        materials.forEach((material) => material && (material.side = THREE.DoubleSide));
    });
};

// Centre a model horizontally with its feet at y=0 (so models share a ground
// plane), face it (yaw about the vertical axis) and shift it sideways. Returns
// its footprint width for spacing. OSRS exports face away from the camera, so
// the player's yaw is Math.PI (turn to face the viewer).
const placeModel = (object, offsetX, yaw) => {
    const box = new THREE.Box3().setFromObject(object);
    const center = box.getCenter(new THREE.Vector3());

    object.traverse((child) => {
        if (child.isMesh && child.geometry) {
            child.geometry.translate(-center.x, -box.min.y, -center.z);
        }
    });

    object.rotation.y = yaw;
    object.position.x = offsetX;

    return box.max.x - box.min.x;
};

// Position the player facing the viewer; when fighting, the opponent stands to
// the side turned to face the player (a little combat tableau).
const composeScene = (player, npc) => {
    const group = new THREE.Group();

    prepareMeshes(player);
    const playerWidth = placeModel(player, 0, Math.PI);
    group.add(player);

    if (npc) {
        prepareMeshes(npc);
        const npcBox = new THREE.Box3().setFromObject(npc);
        const npcWidth = npcBox.max.x - npcBox.min.x;
        const offset = playerWidth / 2 + npcWidth / 2 + Math.max(playerWidth, npcWidth) * 0.2;
        placeModel(npc, offset, Math.PI / 2); // turn to face the player (−X)
        group.add(npc);
    }

    scene.add(group);
    frameScene(group);
};

const frameScene = (group) => {
    const box = new THREE.Box3().setFromObject(group);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());

    const maxDimension = Math.max(size.x, size.y, size.z) || 1;
    const distance = (maxDimension / 2) / Math.tan((camera.fov * Math.PI) / 360) * 1.3;

    camera.position.set(center.x, center.y, center.z + distance);
    camera.near = distance / 100;
    camera.far = distance * 100;
    camera.updateProjectionMatrix();
    camera.lookAt(center);

    controls.target.copy(center);
    controls.update();
};

const loadModel = (objUrl, mtlUrl) => new Promise((resolve, reject) => {
    const objLoader = new OBJLoader();
    if (mtlUrl) {
        new MTLLoader().load(mtlUrl, (materials) => {
            materials.preload();
            objLoader.setMaterials(materials);
            objLoader.load(objUrl, resolve, undefined, reject);
        }, undefined, reject);
    } else {
        objLoader.load(objUrl, resolve, undefined, reject);
    }
});

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

    Promise.all([
        loadModel(props.avatar.obj_url, props.avatar.mtl_url),
        props.avatar.npc_obj_url
            ? loadModel(props.avatar.npc_obj_url, props.avatar.npc_mtl_url)
            : Promise.resolve(null),
    ])
        .then(([player, npc]) => {
            if (!scene) {
                return; // Torn down (navigated away / re-init) while loading.
            }
            composeScene(player, npc);
            status.value = "ready";
            animate();
        })
        .catch(() => {
            status.value = "error";
            disposeScene();
        });

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
    <div class="relative bg-base-200 border border-base-300 rounded resource-pack-dialog p-2"
         :class="expanded ? 'h-[30rem]' : 'h-64'">
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

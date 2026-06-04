<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
        <div>
            <slot name="logo" />
        </div>

        <div class="card w-full sm:max-w-md mt-6 bg-base-100 shadow-xl">
            <div class="card-body">
                <slot />
            </div>
        </div>
    </div>
</template>

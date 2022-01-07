<template>
    <div class="shadow" @click='$emit("close")'>
        <div class="modal">
            <img :src="image" class="object-scale-down max-w-screen max-h-screen"/>
        </div>
    </div>
</template>

<script>
    export default {
        emits: ['close'],
        props: {
            image: {
                required: false,
                type: String,
            },
        }
    };
</script>

<style scoped lang="css">
    div.shadow {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        height: 100vh;
        width: 100vw;
        background-color: rgba(0, 0, 0, 0.53);
        display: flex;
        transition: opacity 0.3s ease;
        align-items: center;
        justify-content: center;
    }
</style>

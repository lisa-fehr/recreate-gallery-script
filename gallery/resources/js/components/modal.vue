<template>
    <div class="shadow" @click='$emit("close")'>
        <div class="modal">
            <img :src="image" class="max-w-screen max-h-screen"/>
        </div>
    </div>
</template>

<script>
    export default {
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
        position: absolute;
        top: 0;
        height: 100vh;
        width: 100vw;
        background-color: rgba(0, 0, 0, 0.53);
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

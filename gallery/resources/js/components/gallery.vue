<template>
    <div>
        <div class="flex items-center justify-center h-screen">
            <div class="grid grid-cols-4 gap-2 p-5 bg-yellow-600">
                <thumbnail v-for="image in images" :image="image.thumbnail" :key="image.thumbnail" @click.native="(currentImage = image.image)"/>
            </div>
        </div>
        <modal v-if="currentImage" :image="currentImage" @close="currentImage=null" />
    </div>
</template>

<script>
    import Thumbnail from "./thumbnail";
    import Modal from "./modal";

    export default {
        components: {Modal, Thumbnail},
        props: {
            images: {
                default: [],
                type: Array,
            }
        },
        data() {
            return {
                currentImage: null
            }
        },
    };
</script>

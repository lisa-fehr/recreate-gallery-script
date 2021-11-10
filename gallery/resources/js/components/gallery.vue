<template>
    <div>
        <div class="flex flex-col h-screen">
            <navigation :filters="filters"></navigation>
            <div class="grid grid-cols-4 gap-2 p-5 bg-yellow-600">
                <thumbnail v-for="image in images" :image="image.thumbnail" :key="image.thumbnail" @click.native="(currentImage = image.image)"/>
            </div>
        </div>
        <modal v-if="currentImage" :image="currentImage" @close="currentImage=null" />
    </div>
</template>

<script>
    import Navigation from "../components/navigation";

    import Thumbnail from "./thumbnail";
    import Modal from "./modal";

    export default {
        components: {Navigation, Modal, Thumbnail},
        props: {
            filters: {
                default: null,
                type: String,
            }
        },
        data() {
            return {
                currentImage: null,
                images: [],
            }
        },
        created() {
            this.getImages();
        },
        methods: {
            getImages() {
                let url = '/gallery';
                if (this.filters) {
                    url += '?filter[tags]=' + this.filters;
                }
                axios.get(url).then(response => {
                    this.images = response.data;
                });
            },
        },
    };
</script>

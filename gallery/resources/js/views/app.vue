<template>
    <div>
        <gallery v-if="images" :images="images"/>
    </div>
</template>
<script>
    import Gallery from "../components/gallery";

    export default {
        components: {Gallery},
        data() {
            return {
                images: null,
            }
        },
        created() {
            this.getImages();
        },
        methods: {
            getImages() {
                axios.get('/gallery').then(response => {
                    this.images = response.data;
                });
            }
        },
    };
</script>

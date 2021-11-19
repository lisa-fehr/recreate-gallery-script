<template>
    <div>
        <div class="flex flex-col h-screen">
            <navigation :filters="filters"></navigation>
            <div class="grid grid-cols-6 gap-2 p-5 bg-yellow-600">
                <thumbnail v-for="(image, index) in images" :image="image.thumbnail" :key="`image-${index}`" @click.native="(currentImage = image.image)"/>
            </div>
            <div class="flex items-center">
                <button @click="next()">
                    Next
                </button>

                <button>
                    Previous
                </button>
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
                pagination: null,
            }
        },
        created() {
            this.getImages(1);
        },
        computed: {
            currentPageNumber() {
              return this.pagination ? this.pagination.current_page : 1
            }
        },
        methods: {
            next() {
                this.getImages(this.currentPageNumber + 1);
            },
            galleryUrl(page) {
                let url = '/gallery';
                url += "?page=" + page
                if (this.filters) {
                    url += '&filter[tags]=' + this.filters;
                }

                return url
            },
            getImages(page) {
                axios.get(this.galleryUrl(page)).then(response => {
                    const { data, current_page, first_page_url, from, last_page, last_page_url, links, next_page_url, path, per_page, prev_page_url, to, total } = response.data;
                    this.images = data;
                    this.pagination = { current_page, first_page_url, from, last_page, last_page_url, links, next_page_url, path, per_page, prev_page_url, to, total };
                });
            },
        },
    };
</script>

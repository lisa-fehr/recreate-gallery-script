<template>
    <div>
        <div class="flex flex-col h-screen">
            <navigation :filters="filters"></navigation>
            <pagination @next="next" @previous="previous" @goTo="goToPage" :data="pagination" />
            <div class="grid grid-cols-6 gap-2 p-5 bg-yellow-600">
                <thumbnail v-for="(image, index) in images" :image="image.thumbnail" :key="`image-${index}`" @click.native="(currentImage = image.image)"/>
            </div>
            <pagination @next="next" @previous="previous" @goTo="goToPage" :data="pagination" />
        </div>
        <teleport to="body">
            <modal v-if="currentImage" :image="currentImage" @close="currentImage=null" />
        </teleport>
    </div>
</template>

<script>
    import Navigation from '../components/navigation';

    import Thumbnail from './thumbnail';
    import Modal from './modal';

    import Pagination from './pagination';

    export default {
        components: {Pagination, Navigation, Modal, Thumbnail},
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
            const currentPageFromUrl = this.currentPageFromUrl();
            this.getImages(isNaN(currentPageFromUrl) ? 1 : currentPageFromUrl);

            window.onpopstate = () => {
                window.location.reload();
            };
        },
        watch: {
            currentPageNumber(page) {
                this.getImages(page);
            },
        },
        computed: {
            currentPageNumber() {
                const currentPageFromUrl = this.currentPageFromUrl();
                if (this.pagination && !isNaN(currentPageFromUrl) && currentPageFromUrl !== this.pagination.current_page) {
                    return currentPageFromUrl;
                }
                return this.pagination ? this.pagination.current_page : 1;
            },
        },
        methods: {
            currentPageFromUrl() {
                return parseInt(window.location.search.replace(/[^0-9]/g, ''));
            },
            goToPage({pageNumber, url}) {
                this.pagination.current_page = pageNumber;
                window.history.pushState({}, '', url);
            },
            next(url) {
                this.pagination.current_page = this.currentPageNumber + 1;
                window.history.pushState({}, '', url);
            },
            previous(url) {
                this.pagination.current_page = this.currentPageNumber - 1;
                window.history.pushState({}, '', url);
            },
            galleryUrl(page) {
                let url = '/gallery';
                url += "?page=" + page;
                if (this.filters) {
                    url += '&filter[tags]=' + this.filters;
                }

                return url;
            },
            getImages(page) {
                axios.get(this.galleryUrl(page)).then(response => {
                    const { data, current_page, from, last_page, per_page, to, total } = response.data;
                    this.images = data;
                    this.pagination = { current_page, from, last_page, per_page,to, total };
                });
            },
        },
    };
</script>

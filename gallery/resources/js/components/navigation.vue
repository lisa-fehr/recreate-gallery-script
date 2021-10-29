<template>
    <div>
        <div v-if="!loading" class="flex justify-items-start">
            <div class="flex gap-4 p-5">
                <a v-if="hasCurrent()" :href="parentUrl()" class="inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                    </svg> Back to {{ parentName() }}
                </a>
                <div class="inline-flex">
                    <star :active="true"/> all
                </div>

                <div v-for="nav in navigation.children" :key="nav.name">
                    <a :href="childUrl(nav)" class="inline-flex">
                        <star/> {{ nav.display_name || nav.name }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Star from '../components/star';

    export default {
        components: {Star},
        props: {
            filters: {
                default: null,
                type: String,
            },
        },
        data() {
            return {
                portfolioUrl: '/portfolio/',
                loading: true,
                navigation: {
                    default: () => {},
                    type: Object,
                },
            }
        },
        created() {
            this.getFilters();
        },
        methods: {
            getFilters() {
                axios.get('/tags/' + this.filters).then(response => {
                    this.navigation = response.data;
                    this.removeTheAllTag();
                    this.loading = false;
                });
            },
            removeTheAllTag() {
                if (this.navigation.children[0] && this.navigation.children[0].name === 'all') {
                    this.navigation.children.shift();
                }
            },
            hasCurrent() {
                return this.navigation.current;
            },
            parentName() {
                if (!this.navigation.current.parent) {
                    return 'portfolio';
                }
                return this.navigation.current.parent.name;
            },
            parentUrl() {
                if (!this.navigation.current.parent) {
                    return this.portfolioUrl;
                }
                return this.portfolioUrl + this.navigation.current.parent.name;
            },
            childUrl(nav) {
                if (nav.display_name) {
                    return this.portfolioUrl + nav.parent.name + '/' + nav.display_name;
                }
                return this.portfolioUrl + nav.name;
            }
        }
    };
</script>

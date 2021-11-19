<template>
    <div>
        <div v-if="!loading" class="">
            <a v-if="hasCurrent()" :href="parentUrl()" class="pl-5 pt-5 flex w-full items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                </svg>
                Back to {{ parentName() }}
            </a>
            <div class="flex">
                <div class="p-5 flex w-32">
                    <star :active="true"/> all
                </div>
                <a v-for="nav in navigation.children" :key="nav.name" class="p-5 flex w-32"
                   :href="childUrl(nav)">
                        <star/> {{ nav.display_name || nav.name }}
                </a>
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

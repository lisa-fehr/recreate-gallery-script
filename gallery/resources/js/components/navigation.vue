<template>
    <div>
        <div v-if="!loading">
            <a v-if="hasCurrent()" :href="parentUrl()" class="pl-5 pt-5 flex w-full items-center">
                <arrow>
                    Back to {{ parentName() }}
                </arrow>
            </a>
            <div class="flex pl-2 bg-gray-100 bg-opacity-75">
                <div class="p-5 flex w-32">
                    <star active/>
                    all
                </div>
                <a v-for="nav in navigation.children" :key="nav.name" class="p-5 flex w-32"
                   :href="childUrl(nav)">
                    <star/>
                    {{ nav.display_name || nav.name }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import Star from '../components/star';
    import Arrow from '../components/arrow';

    export default {
        components: {Star, Arrow},
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
                    default: () => {
                    },
                    type: Object,
                },
            }
        },
        created() {
            this.getFilters();
        },
        methods: {
            getFilters() {
                axios.get('/tags/' + (this.filters ?? '')).then(response => {
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

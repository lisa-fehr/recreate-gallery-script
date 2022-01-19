<template>
    <div class="flex w-full justify-between px-5" v-if="data">
        <a :href="previousUrl" @click.prevent="$emit('previous', previousUrl)" :disabled="disablePrevious"
           class="py-1"
           :class="{'cursor-not-allowed  text-neutral-500': disablePrevious, 'hover:text-orange-400': !disablePrevious}">
            <arrow>Previous</arrow>
        </a>
        <div>
            Page: <select :value="data.current_page" @change="$emit('goTo', { pageNumber: $event.target.value, url:  baseUrl + '/?' +  $event.target.value})" class="shadow border rounded p-1">
                <option v-for="n in data.last_page" :key="n" :value="Number(n)">{{ n }}</option>
            </select>
        </div>
        <a :href="nextUrl" @click.prevent="$emit('next', nextUrl)" :disabled="disableNext"
           class="py-1"
           :class="{'cursor-not-allowed text-neutral-500': disableNext, 'hover:text-orange-400': !disableNext}">
            <arrow direction="forward">Next</arrow>
        </a>
    </div>
</template>

<script>
    import Arrow from '../components/arrow';
    export default {
        name: 'pagination',
        emits: ['previous', 'goTo', 'next'],
        components: {Arrow},
        props: {
            data: {
                type: [Object, null],
                default: {}
            }
        },
        methods: {
            navigateTo(direction)
            {
                if ((this.data.current_page + direction > this.data.last_page) || (this.data.current_page + direction < 1)) {
                    return null;
                }
                return this.baseUrl + '/?' + (this.data.current_page + direction);
            }
        },
        computed: {
            baseUrl() {
                return window.location.pathname.replace(/\/+$/, '');
            },
            nextUrl() {
                return this.navigateTo(+ 1);
            },
            previousUrl() {
                return this.navigateTo(- 1);
            },
            total() {
                if (!this.data) {
                    return 0;
                }
                return Math.round(this.data.total / this.data.per_page);
            },
            disablePrevious() {
                return this.data.current_page <= 1 || this.data.current_page > this.data.last_page ;
            },
            disableNext() {
                return this.data.current_page >= this.data.last_page;
            }
        }
    }
</script>

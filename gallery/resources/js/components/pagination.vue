<template>
    <div class="flex w-full justify-between px-5" v-if="data">
        <button @click="$emit('previous')" :disabled="data.current_page === 1">
            <arrow>Previous</arrow>
        </button>
        <div>
            Page: <select :value="data.current_page" @change="$emit('goTo', $event.target.value)">
                <option v-for="n in data.last_page" :key="n" :value="Number(n)">{{ n }}</option>
            </select>
        </div>
        <button @click="$emit('next')" :disabled="data.current_page === data.last_page">
            <arrow direction="forward">Next</arrow>
        </button>
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
        computed: {
            total() {
                if (!this.data) {
                    return 0;
                }
                return Math.round(this.data.total / this.data.per_page);
            }
        }
    }
</script>

<style scoped>

</style>

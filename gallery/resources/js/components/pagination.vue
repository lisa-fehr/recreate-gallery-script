<template>
    <div class="flex w-full justify-between px-5">
        <arrow>
            <button @click="$emit('previous')">
                Previous
            </button>
        </arrow>
        <div v-if="data">
            Page: <select>
                <option v-for="n in total" :key="n">{{ n }}</option>
            </select>
        </div>
        <arrow direction="forward">
            <button @click="$emit('next')">
                Next
            </button>
        </arrow>
    </div>
</template>

<script>
    import Arrow from '../components/arrow';
    export default {
        name: 'pagination',
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

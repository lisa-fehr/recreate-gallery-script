<template>
    <img v-if="image" class="w-full" :src="image"/>
</template>

<script>
    export default {
        props: {
            image: {
                required: true,
                type: String,
            }
        }
    };
</script>

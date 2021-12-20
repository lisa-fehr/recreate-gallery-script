<template>
    <div>
        <gallery :filters="filters"/>
    </div>
</template>
<script>
    import Gallery from "../components/gallery";

    export default {
        components: {Gallery},
        props: {
            filters: {
                type: String,
            }
        }
    };
</script>

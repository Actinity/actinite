<template>

    <label class="btn btn-primary file-upload">
        <input type="file" ref="input" @change="upload" />
        <i class="fas fa-upload"></i> Upload
    </label>


</template>
<script>
export default {
    methods: {
        reset() {
            this.$refs.input.value = null;
        },
        upload(event) {
            let file = event.target.files[0], fd = new FormData();
            fd.append('file',file);

            if(file.size >= 8192000) {
                alert('Sorry, you can only currently upload files smaller than 8MB');
                this.reset();
                return;
            }

            axios.post('/actinite/api/assets',fd)
                .then(r => {
                    this.$emit('upload',r.data);
                    this.reset();
                })
                .catch(e => {
                    alert('Sorry, unable to upload');
                    console.log(e,e.response);
                });
        }
    }
}
</script>

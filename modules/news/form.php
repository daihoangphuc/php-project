<!-- Thêm TinyMCE -->
<script src="https://cdn.tiny.cloud/1/your-api-key/tinymce/5/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    plugins: 'link image table lists',
    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image'
});
</script> 
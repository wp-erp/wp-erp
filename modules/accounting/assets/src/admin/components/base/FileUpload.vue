<template>
    <form enctype="multipart/form-data" novalidate>
        <div class="attachment-placeholder"> To attach
            <input type="file" id="attachment" multiple accept="image/*"
                :name="uploadFieldName"
                :disabled="isSaving"
                @change="filesChange($event)" class="display-none">
            <label for="attachment">Select files</label> from your computer
            <span v-if="isSaving" class="upload-count"> (uploading {{ fileCount }} files...)</span>
            <span v-if="isUploaded" class="upload-count"> (uploaded {{ fileCount }} files...)</span>
        </div>
    </form>
</template>

<script>
    import HTTP from 'admin/http';

    const STATUS_INITIAL = 0,
        STATUS_SAVING = 1,
        STATUS_SUCCESS = 2,
        STATUS_FAILED = 3;

    export default {
        name: 'FileUpload',

        data() {
            return {
                fileCount: 0,
                isUploaded: false,
                uploadedFiles: [],
                uploadError: null,
                currentStatus: null,
                uploadFieldName: 'photos[]'
            }
        },

        props: {
            url: {
                type: String,
                required: true
            }
        },

        computed: {
            isInitial() {
                return this.currentStatus === STATUS_INITIAL;
            },

            isSaving() {
                return this.currentStatus === STATUS_SAVING;
            },

            isSuccess() {
                return this.currentStatus === STATUS_SUCCESS;
            },

            isFailed() {
                return this.currentStatus === STATUS_FAILED;
            }
        },
        methods: {
            reset() {
                this.currentStatus = STATUS_INITIAL;
                this.uploadedFiles = [];
                this.uploadError = null;
            },

            save(formData) {
                this.currentStatus = STATUS_SAVING;

                this.upload(formData)
                    .then(x => {
                        this.uploadedFiles = [].concat(x);
                        this.currentStatus = STATUS_SUCCESS;

                        this.isUploaded = true;
                    })
                    .catch(err => {
                        this.uploadError = err.response;
                        this.currentStatus = STATUS_FAILED;
                    });
            },

            filesChange(event) {
                const formData = new FormData();

                let fieldName = event.target.name;
                let fileList = event.target.files;

                if ( ! fileList.length ) return;

                this.fileCount = fileList.length;

                // append the files to FormData
                Array
                    .from(Array(fileList.length).keys())
                    .map(x => {
                        formData.append(fieldName, fileList[x], fileList[x].name);
                    });

                this.save(formData);
            },

            upload(formData) {
                const BASE_URL = erp_acct_var.site_url;

                let url = `${BASE_URL}/wp-json/erp/v1/accounting/v1${this.url}`;

                HTTP.post(url, formData).then( res => {
                    console.log(res);
                });

                // let a=  HTTP.post(url, formData)
                //     .then(x => x.data)
                //     .then(x => x.map(img => {
                //         Object.assign({}, img, { url: `${BASE_URL}/images/${img.id}` })
                //     })
                // );

                // console.log(a);
            },

        },

        mounted() {
            this.reset();
        }

    }
</script>

<style>
    .upload-count {
        color: #f44336;
    }
</style>


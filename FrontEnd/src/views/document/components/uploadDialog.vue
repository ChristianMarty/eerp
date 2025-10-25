<template>
  <div class="document-upload-dialog">

    <el-dialog
      title="Document Upload"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >
      <el-form class="form-container">
        <el-form-item>
          <el-upload
            ref="upload"
            :action="docUrl"
            :file-list="fileList"
            drag
            multiple
            :auto-upload="false"
            :on-success="onUploadSuccess"
            :on-error="onUploadError()"
          >
            <i class="el-icon-upload" />
            <div class="el-upload__text">
              Drop file here or <em>click to upload</em>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="onUpload()">Upload</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>

export default {
  name: 'DocumentUploadDialog',
  props: { inventoryNumber: { type: String, default: '' },
    accessoryNumber: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      docUrl: process.env.VUE_APP_BLUENOVA_API + '/document/ingest/upload',
      fileList: []
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {

    },
    onUploadSuccess(response, file, fileList) {
      this.closeDialog()
      if (response.error === null) {
        this.$message({
          showClose: true,
          message: response.data.message,
          duration: 2,
          type: 'success'
        })
      } else {
        this.$message({
          showClose: true,
          duration: 10,
          message: response.error,
          type: 'error'
        })
      }
    },
    onUploadError(err, file, fileList) {
    },
    onUpload() {
      this.$refs.upload.submit()
    },
    closeDialog() {
      this.visible = false
      this.$refs.upload.clearFiles()
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>

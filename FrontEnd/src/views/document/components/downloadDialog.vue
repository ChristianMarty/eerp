<template>
  <div class="document-download-dialog">

    <el-dialog
      title="Document Download"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
    >
      <p>Download a PDF file from the Internet.</p>
      <el-form class="form-container">
        <el-form-item label="URL:">
          <el-input v-model="downloadURL" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="onDownload()">Download</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script>

import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentDownloadDialog',
  props: { visible: { type: Boolean, default: false }},
  data() {
    return {
      downloadURL: ''
    }
  },
  mounted() {
  },
  methods: {
    onDownload() {
      document.ingest.download(this.downloadURL).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>

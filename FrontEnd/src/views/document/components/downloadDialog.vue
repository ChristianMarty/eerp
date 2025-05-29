<template>
  <div>
    <el-dialog
      title="Document Download"
      :visible.sync="visible"
      :before-close="close"
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
  methods: {
    onDownload() {
      document.ingest.download(this.downloadURL).then(response => {
        this.downloadURL = ''
        this.close()
      })
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>

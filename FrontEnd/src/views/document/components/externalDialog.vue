<template>
  <div>
    <el-dialog
      title="Add External Link"
      :visible.sync="visible"
      :before-close="close"
      center
    >
      <p>Link an external file.</p>
      <el-form class="form-container">
        <el-form-item label="URL:">
          <el-input v-model="externalUrl" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="onAdd()">Add External Link</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script>

import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentExternalDialog',
  props: { visible: { type: Boolean, default: false }},
  data() {
    return {
      externalUrl: ''
    }
  },
  methods: {
    onAdd() {
      document.ingest.external(this.externalUrl).then(response => {
        this.externalUrl = ''
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

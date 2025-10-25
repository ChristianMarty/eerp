<template>
  <div class="generic-document-ingest-container">

    <el-form label-width="180px">
      <el-form-item label="File Name:">
        {{ dialogData.IngestName }}
      </el-form-item>

      <el-form-item label="Mode:">
        <el-select v-model="selectedMode">
          <el-option value="newDocument" label="New Document" />
          <el-option value="newRevision" label="New Revision" />
        </el-select>
      </el-form-item>

      <el-form-item v-if="selectedMode === 'newRevision'" label="Document Code:">
        <el-input v-model="dialogData.DocumentNumber" />
      </el-form-item>

      <el-form-item v-if="selectedMode === 'newDocument'" label="Name:">
        <el-input v-model="dialogData.Name" />
      </el-form-item>

      <el-form-item v-if="selectedMode === 'newDocument'" label="Category:">
        <el-select v-model="dialogData.Category" filterable>
          <el-option
            v-for="item in documentCategoryOptions"
            :key="item"
            :label="item"
            :value="item"
          />
        </el-select>
      </el-form-item>

      <el-form-item v-if="selectedMode === 'newDocument'"  label="Document Description:">
        <el-input v-model="dialogData.DocumentDescription" />
      </el-form-item>

      <el-form-item label="Revision Description:">
        <el-input v-model="dialogData.RevisionDescription" />
      </el-form-item>

    </el-form>

  </div>
</template>

<script>

import Document from '@/api/document'
const document = new Document()

export default {
  props: {
    fileInfo: { type: Object, default: null }
  },
  data() {
    return {
      dialogData: Object.assign({}, document.ingest.ingestParameters),
      documentCategoryOptions: [],
      selectedMode: 'newDocument'
    }
  },
  created() {
  },
  async mounted() {
    this.documentCategoryOptions = await document.category()
    this.dialogData.IngestName = this.fileInfo.FileName
    this.dialogData.Name = this.dialogData.IngestName.replace(/\.[^/.]+$/, '')
  },
  methods: {

    ingest() {
      document.ingest.ingest(this.dialogData).then(response => {
        this.$message({
          showClose: true,
          message: 'Changes saved successfully',
          duration: 1500,
          type: 'success'
        })
        this.$emit('success')
      })
    }
  }
}

</script>

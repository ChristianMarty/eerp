<template>
  <div class="generic-document-ingest-container">

    <el-form label-width="120px">
      <el-form-item label="Name:">
        {{ fileInfo.FileName }}
      </el-form-item>

      <el-form-item label="Name:">
        <el-input v-model="dialogData.Name" />
        <p>Please follow the naming convention!</p>
      </el-form-item>

      <el-form-item label="Type:">
        <el-select v-model="dialogData.Type" filterable>
          <el-option
            v-for="item in documentTypeOptions"
            :key="item"
            :label="item"
            :value="item"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="dialogData.Description" />
      </el-form-item>

      <el-form-item label="Note:">
        <el-input v-model="dialogData.Note" type="textarea" />
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
      dialogData: Object.assign({}, document.ingestParameters),
      documentTypeOptions: []
    }
  },
  created() {
  },
  async mounted() {
    this.documentTypeOptions = await document.types()
    this.dialogData.FileName = this.fileInfo.FileName
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

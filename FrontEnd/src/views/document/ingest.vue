<template>
  <div class="app-container">
    <h1>Document Ingestion</h1>
    <p>Select a document to import it into the system.</p>
    <el-button type="primary" @click="getFileList()">Reload</el-button>
    <el-table
      :data="documentList"
      style="width: 100%"
      @row-click="(row, column, event) =>openDialog(row)"
    >

      <el-table-column prop="FileName" label="Name" sortable />
      <el-table-column prop="Date" label="Date" width="200" sortable />
      <el-table-column prop="Size" label="Size" width="200" sortable />
    </el-table>

    <el-dialog title="Ingest Document" :visible.sync="showDialog" center width="80%">

      <el-row :gutter="20">

        <el-col :span="12">
          <iframe :src="filePreviewPath" width="100%" height="500px" />
        </el-col>

        <el-col :span="12">
          <el-form label-width="120px">
            <el-form-item label="Name:">
              {{ dialogData.FileName }}
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

          </el-form>

        </el-col>

      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="deleteFile()">Delete</el-button>
        <el-button type="primary" @click="ingestFile()">Ingest</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentIngest',
  components: { },
  data() {
    return {
      documentList: [],
      documentTypeOptions: [],
      showDialog: false,
      dialogData: Object.assign({}, document.ingestParameters),
      filePreviewPath: ''
    }
  },
  async mounted() {
    this.getFileList()
    this.documentTypeOptions = await document.types()
  },
  methods: {
    openDialog(row) {
      this.showDialog = true
      this.dialogData = Object.assign({}, document.ingestParameters)
      this.dialogData.FileName = row.FileName
      this.filePreviewPath = row.Path
    },
    ingestFile() {
      document.ingest.ingest(this.dialogData).then(response => {
        this.$message({
          showClose: true,
          message: 'Changes saved successfully',
          duration: 1500,
          type: 'success'
        })
        this.showDialog = false
        this.getFileList()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    deleteFile() {
      this.$confirm('This will permanently delete the file. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        document.ingest.delete(this.dialogData).then(response => {
          this.$message({
            showClose: true,
            message: 'Delete completed',
            duration: 1500,
            type: 'success'
          })
          this.showDialog = false
          this.getFileList()
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 1500,
            type: 'error'
          })
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        })
      })
    },
    getFileList() {
      document.ingest.search().then(response => {
        this.documentList = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>

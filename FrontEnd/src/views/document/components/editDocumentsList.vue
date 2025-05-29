<template>
  <div class="list-document-edit-container">
    <el-button
      type="primary"
      icon="el-icon-edit"
      circle
      @click="openDialog()"
    />

    <el-dialog title="Select Document" :visible.sync="visible" width="80%" center>
      <el-scrollbar wrap-style="max-height: 600px;">
        <el-table
          :data="documentOptions"
          style="width: 100%;  "
          :row-key="DocumentNumber"
        >
          <el-table-column label="Add" width="60">
            <template slot-scope="{ row }">
              <template v-if="row.Selected === true">
                <el-button
                  type="success"
                  icon="el-icon-check"
                  circle
                  @click="handleAdd(row)"
                />
              </template>
              <template v-else>
                <el-button
                  type="primary"
                  icon="el-icon-plus"
                  circle
                  @click="handleAdd(row)"
                />
              </template>
            </template>
          </el-table-column>
          <el-table-column label="Open" width="60">
            <template slot-scope="{ row }">
              <el-button
                type="info"
                icon="el-icon-view"
                circle
                @click="open(row.Path)"
              />
            </template>
          </el-table-column>
          <el-table-column prop="ItemCode" label="Code" width="100" />
          <el-table-column prop="Name" label="Document" />
          <el-table-column prop="Note" label="Note" />
          <el-table-column prop="Type" label="Type" width="120" />
          <el-table-column prop="CreationDate" label="Upload Timestamp" width="180" />
        </el-table>
      </el-scrollbar>

      <p><b>Selected Documents:</b></p>
      <el-tag
        v-for="tag in selectedDocuments"
        :key="tag.DocumentNumber"
        closable
        type="info"
        @close="handleRemove(tag)"
      >
        {{ tag.Name }}
      </el-tag>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save()">Save</el-button>
        <el-button @click="visible = false">Cancel</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import Document from '@/api/document'
const document = new Document()

export default {
  props: { attach: { type: String, default: null }, barcode: { type: String, default: null }},
  data() {
    return {
      visible: false,
      documentOptions: [],
      selectedDocuments: []
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    openDialog() {
      this.getDocuments()
      this.getDocumentOptions()
      this.visible = true
    },
    handleRemove(tag) {
      this.selectedDocuments.splice(this.selectedDocuments.indexOf(tag), 1)
    },
    handleAdd(row) {
      if (!this.selectedDocuments.some(el => el.DocumentNumber === row.DocumentNumber)) {
        this.selectedDocuments.push({ DocumentNumber: row.DocumentNumber, Name: row.Name })

        const docIndex = this.documentOptions.findIndex(el => el.DocumentNumber === row.DocumentNumber)
        this.documentOptions[docIndex].Selected = true

        console.log(this.documentOptions[docIndex])
      }
    },
    open(path) {
      window.open(path, '_blank').focus()
    },
    save() {
      const DocumentNumberList = []
      this.selectedDocuments.forEach(element => {
        DocumentNumberList.push(element.DocumentNumber)
      })

      const attachParameters = {
        Table: this.$props.attach,
        DocumentBarcodes: DocumentNumberList,
        AttachBarcode: this.$props.barcode
      }
      document.attachment.attach(attachParameters).then(response => {
        this.visible = false
        this.$emit('update:visible', this.visible)
        this.$emit('change')
      })
    },
    getDocuments() {
      const attachSearchParameters = {
        Table: this.$props.attach,
        AttachBarcode: this.$props.barcode
      }
      document.attachment.search(attachSearchParameters).then(response => {
        this.selectedDocuments = response
      })
    },
    getDocumentOptions() {
      document.list().then(response => {
        this.documentOptions = response
        this.documentOptions.forEach((el, index) => {
          el.Selected = false
          this.documentOptions[index] = el
        })
      })
    }
  }
}

</script>

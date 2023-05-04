<template>
  <div class="app-container">
    <h1>Document Ingestion</h1>
    <p>Select a document to import it into the system.</p>
    <template v-if="checkPermission(['document.upload'])">
      <el-button type="primary" icon="el-icon-upload" @click="uploadFile()">Upload</el-button>
    </template>
    <template v-if="checkPermission(['document.upload'])">
      <el-button type="primary" icon="el-icon-download" @click="downloadFile()">Download</el-button>
    </template>
    <el-button type="primary" icon="el-icon-refresh-right" @click="getFileList()">Reload</el-button>
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
          <el-button
            type="info"
            icon="el-icon-document"
            @click="openInTab(filePreviewPath)"
          >View file in new tab</el-button>
        </el-col>

        <el-col :span="12">

          <el-form label-width="120px">
            <el-form-item label="Template:">
              <el-select v-model="selectedTemplate" placeholder="Select Template" style="width: 100%">
                <el-option
                  v-for="item in templateOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
          </el-form>

          <genericIngest
            v-if="selectedTemplate == 'genereic'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poDeliveryNoteIngest
            v-if="selectedTemplate == 'poDeliveryNote'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poInvoiceIngest
            v-if="selectedTemplate == 'poInvoice'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poReceiptIngest
            v-if="selectedTemplate == 'poReceipt'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poQuoteIngest
            v-if="selectedTemplate == 'poQuote'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poQuoteConfirmation
            v-if="selectedTemplate == 'poConfirmation'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <poQuoteApproval
            v-if="selectedTemplate == 'poApproval'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

          <invHistoryCalibration
            v-if="selectedTemplate == 'invCalibration'"
            ref="ingestForm"
            :file-info="dialogData"
            @success="ingestSuccess()"
          />

        </el-col>

      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="deleteFile()">Delete</el-button>
        <el-button type="primary" :disabled="selectedTemplate == null" @click="ingestFile()">Ingest</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>
      </span>
    </el-dialog>

    <uploadDialog
      :visible.sync="uploadDialogVisible"
      @change="getFileList()"
    />

    <downloadDialog
      :visible.sync="downloadDialogVisible"
      @change="getFileList()"
    />

  </div>
</template>

<script>
import uploadDialog from './components/uploadDialog'
import downloadDialog from './components/downloadDialog'
import checkPermission from '@/utils/permission'

import genericIngest from './components/ingestTemplates/generic'
import poDeliveryNoteIngest from './components/ingestTemplates/purchaseOrderDeliveryNote'
import poInvoiceIngest from './components/ingestTemplates/purchaseOrderInvoice'
import poReceiptIngest from './components/ingestTemplates/purchaseOrderReceipt'
import poQuoteIngest from './components/ingestTemplates/purchaseOrderQuote'
import poQuoteConfirmation from './components/ingestTemplates/purchaseOrderConfirmation'
import poQuoteApproval from './components/ingestTemplates/purchaseOrderApproval'
import invHistoryCalibration from './components/ingestTemplates/inventoryHistoryCalibration'

import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentIngest',
  components: { uploadDialog, downloadDialog, genericIngest, poDeliveryNoteIngest, poInvoiceIngest, poReceiptIngest, poQuoteIngest, poQuoteConfirmation, poQuoteApproval, invHistoryCalibration },
  data() {
    return {
      documentList: [],
      showDialog: false,
      dialogData: Object.assign({}, document.ingestParameters),
      filePreviewPath: '',
      uploadDialogVisible: false,
      downloadDialogVisible: false,
      selectedTemplate: null,
      templateOptions: [{
        value: 'genereic',
        label: 'Generic'
      }, {
        value: 'poDeliveryNote',
        label: 'Purchase Order Delivery Note'
      }, {
        value: 'poInvoice',
        label: 'Purchase Order Invoice'
      }, {
        value: 'poReceipt',
        label: 'Purchase Order Receipt'
      }, {
        value: 'poQuote',
        label: 'Purchase Order Quote'
      }, {
        value: 'poConfirmation',
        label: 'Purchase Order Confirmation'
      }, {
        value: 'poApproval',
        label: 'Purchase Order Approval'
      }, {
        value: 'invCalibration',
        label: 'Inventory History Calibration'
      }]
    }
  },
  async mounted() {
    this.getFileList()
  },
  methods: {
    checkPermission,
    openDialog(row) {
      this.selectedTemplate = null
      this.showDialog = true
      this.dialogData = Object.assign({}, document.ingestParameters)
      this.dialogData.FileName = row.FileName
      this.filePreviewPath = row.Path
    },
    ingestFile() {
      this.$refs.ingestForm.ingest()
    },
    ingestSuccess() {
      this.showDialog = false
      this.getFileList()
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
    },
    uploadFile() {
      this.uploadDialogVisible = true
    },
    downloadFile() {
      this.downloadDialogVisible = true
    },
    openInTab(path) {
      window.open(path, '_blank').focus()
    }
  }
}
</script>

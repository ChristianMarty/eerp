<template>
  <div class="list-document-container">
    <el-button
      v-if="edit == true"
      type="primary"
      icon="el-icon-plus"
      circle
      @click="openDialog()"
    />
    <el-table
      :data="documents"
      style="width: 100%"
      @row-click="(row)=>open(row.Path)"
    >
      <el-table-column prop="Barcode" label="Doc No" width="100" />
      <el-table-column prop="Description" label="Description">
        <template slot-scope="{ row }">
          {{ row.Description }}
        </template>
      </el-table-column>
      <el-table-column prop="Note" label="Note" />
      <el-table-column prop="Type" label="Type" width="100" />
      <el-table-column prop="Document" label="Document">
        <template slot-scope="{ row }">

          <el-button
            icon="el-icon-document"
            @click="open(row.Path)"
          >Open in new tab</el-button>

        </template>
      </el-table-column>
      <el-table-column prop="LinkType" label="Link" width="100" />
      <el-table-column prop="CreationDate" label="Upload Timestamp" width="180" />
    </el-table>

    <el-dialog title="Select Document" :visible.sync="showDialog" width="50%" center>
      <div style="text-align: center">
        <el-transfer
          v-model="value"
          style="text-align: left; display: inline-block"
          :data="documentOptions"
          :titles="['Available', 'Attached']"
        /></div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save()">Save</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { documents: { type: Object, default: null }, edit: { type: Boolean, default: false }},
  data() {
    return {
      showDialog: false,
      documentOptions: [],
      value: []
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    openDialog() {
      this.addLine()
      this.getDocuments()
      this.showDialog = true
    },
    open(path) {
      window.open(path, '_blank').focus()
    },
    addLine() {

    },
    save() {

    },
    getDocuments() {
      requestBN({
        url: '/document',
        methood: 'get'
      }).then(response => {
        response.data.forEach(element => {
          console.log(element.LinkType)

          this.documentOptions.push({
            label: element.Description,
            key: element.DocNo,
            disabled: false
          })
        })
      })
    }
  }
}

</script>

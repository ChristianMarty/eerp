<template>
  <div class="dashboard-container">
    <el-table
      :data="data"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="Title" sortable label="Process" width="240">
        <template slot-scope="{ row }">
          <el-button @click="run(row)">
            {{ row.Title }}
          </el-button>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

    <el-dialog title="Process Parameter" :visible.sync="showDialog" width="50%" center>
      <el-form size="mini" label-width="220px">
        <template v-for="item in selectedProcess.Parameter">
          <el-form-item :label="item.Name">
            <el-input v-model="formData.Data" type="textarea" />
          </el-form-item>
        </template>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="upload()">Confirm</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'Processes',
  data() {
    return {
      showDialog: false,
      formData: {},
      data: null,
      selectedProcess: {}
    }
  },
  computed: {},
  created() {},
  mounted() {
    this.getData()
  },
  methods: {
    upload() {
      requestBN({
        method: 'post',
        url: this.selectedProcess.Path,
        data: this.formData
      }).then(response => {

      })
    },
    run(process) {
      if (process.Parameter != null) {
        this.selectedProcess = process
        this.showDialog = true
      } else {
        window.open(process.Path, '_blank').focus()
      }
    },
    getData() {
      requestBN({
        url: '/process',
        methood: 'get'
      }).then(response => {
        this.data = response.data
      })
    }
  }
}
</script>

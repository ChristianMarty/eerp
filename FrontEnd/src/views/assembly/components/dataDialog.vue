<template>
  <div class="assembly-history-data-dialog">
    <el-dialog
      title="Assembly History"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="getHistoryData()"
    >

      <p><b>{{ data.Title }}</b></p>
      <p>{{ data.Date }}</p>
      <p>{{ data.Description }}</p>

      <p><b>Data</b></p>
      <el-table
        :data="tableData"
        border
        style="width: 100%"
        :header-cell-style="{ padding: '0', height: '20px' }"
        :cell-style="{ padding: '0', height: '20px' }"
        default-expand-all
        row-key="id"
      >
        <el-table-column prop="key" label="Key" sortable />
        <el-table-column prop="value" label="Value" sortable />
      </el-table>

    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'AssemblyItemHistoryData',
  props: { id: { type: Number, default: 0 }, visible: { type: Boolean, default: false }},
  data() {
    return {
      data: {},
      tableData: []
    }
  },
  mounted() {
  },
  methods: {
    getHistoryData() {
      requestBN({
        url: '/assembly/history/item',
        methood: 'get',
        params: {
          AssemblyHistoryId: this.$props.id
        }
      }).then(response => {
        this.data = response.data
        this.tableData = []
        if (this.data.Data === null) return

        var id = 0

        Object.entries(this.data.Data).forEach(([key, value]) => {
          if (typeof value === 'object') {
            var subTemp = []
            Object.entries(value).forEach(([key, value]) => {
              var temp = { id: id, key: key, value: value }
              subTemp.push(temp)
              id++
            })
            var temp = { key: key, value: '', children: subTemp }
            this.tableData.push(temp)
          } else {
            var temp2 = { id: id, key: key, value: value }
            this.tableData.push(temp2)
            id++
          }
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>

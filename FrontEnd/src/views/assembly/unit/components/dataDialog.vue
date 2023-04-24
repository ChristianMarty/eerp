<template>
  <div class="assembly-history-data-dialog">
    <el-dialog
      :title="data.AssemblyUnitHistoryBarcode + ' - Assembly History'"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >

      <p><b>{{ data.Title }}</b></p>
      <p>{{ data.AssemblyUnitHistoryBarcode }}</p>
      <p>{{ data.Type }}, {{ data.Date }}</p>
      <p>{{ data.Description }}</p>

      <p><b>Shipping</b></p>
      <p>Clearance: {{ data.ShippingClearance }}</p>
      <p>Prohibited: {{ data.ShippingProhibited }}</p>

      <p><b>Data</b></p>
      <el-table
        :data="tableData"
        style="width: 100%; margin-bottom: 20px"
        :header-cell-style="{ padding: '0', height: '20px' }"
        :cell-style="{ padding: '0', height: '20px' }"
        default-expand-all
        row-key="id"
        border
        :tree-props="{ children: 'children' }"
      >
        <el-table-column prop="key" label="Key" sortable />
        <el-table-column prop="value" label="Value" sortable />
      </el-table>

      <el-select v-model="selectedTemplateId">
        <el-option v-for="item in printTemplate" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
      </el-select>

      <el-select v-model="selectedPrinterId">
        <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
      </el-select>
      <el-button type="primary" style="margin-left: 20px" @click="print()">Print</el-button>

    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import * as defaultSetting from '@/utils/defaultSetting'

import Assembly from '@/api/assembly'
const assembly = new Assembly()

export default {
  name: 'AssemblyItemHistoryData',
  props: { assemblyUnitHistoryNumber: { type: Number, default: 0 }, visible: { type: Boolean, default: false }},
  data() {
    return {
      data: {},
      tableData: [],
      selectedPrinterId: 0,
      selectedTemplateId: 0,
      printTemplate: {},
      printer: {}
    }
  },
  mounted() {
    this.getPrintTemplate()
    this.getPrinter()
  },
  methods: {
    onOpen() {
      this.getHistoryData()
    },
    getHistoryData() {
      assembly.unit.history.item(this.$props.assemblyUnitHistoryNumber).then(response => {
        this.data = response
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
    getPrintTemplate() {
      requestBN({
        url: '/label',
        methood: 'get',
        params: { Tag: 'Assembly' }
      }).then(response => {
        this.printTemplate = response.data
      })
    },
    print() {
      assembly.unit.history.item(this.$props.assemblyUnitHistoryNumber).then(response => {
        requestBN({
          method: 'post',
          url: '/print/assemblyBonPrint',
          data: { data: response, PrinterId: this.selectedPrinterId }
        }).then(response => {
          if (response.error !== null) {
            this.$message({
              showClose: true,
              duration: 0,
              message: response.error,
              type: 'error'
            })
          }
        })
      })
    },
    getPrinter() {
      requestBN({
        url: '/printer',
        methood: 'get'
      }).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().AssemblyReportPrinter
        this.selectedTemplateId = defaultSetting.defaultSetting().AssemblyReportTemplate

        this.printer = response.data
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>

<template>
  <div class="assembly-history-data-dialog">
    <el-dialog
      :title="data.ItemCode + ' - Assembly History'"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >

      <p><b>{{ data.Title }}</b></p>
      <p>{{ data.ItemCode }}</p>
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

      <span slot="footer" class="dialog-footer">
        <el-select v-model="selectedRendererId" style="margin-left: 20px">
          <el-option v-for="item in rendererList" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
        <el-select v-model="selectedPrinterId" style="margin-left: 20px">
          <el-option v-for="item in printerList" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
        <el-button type="primary" style="margin-left: 20px" @click="print()">Print</el-button>
        <el-button style="margin-left: 50px" @click="closeDialog">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import * as defaultSetting from '@/utils/defaultSetting'

import Assembly from '@/api/assembly'
const assembly = new Assembly()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Print from '@/api/print'
const print = new Print()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

export default {
  name: 'AssemblyItemHistoryData',
  props: { assemblyUnitHistoryNumber: { type: String, default: '' }, visible: { type: Boolean, default: false }},
  data() {
    return {
      data: {},
      tableData: [],

      printerList: [],
      selectedPrinterId: 0,

      rendererList: [],
      selectedRendererId: 0

    }
  },
  mounted() {
    this.getPrinter()
    this.getRenderer()
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
    print() {
      print.print(this.selectedRendererId, this.selectedPrinterId, [this.$props.assemblyUnitHistoryNumber]).then(response => {
      })
    },
    getPrinter() {
      peripheral.list(peripheral.Type.Printer).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().Assembly.Renderer.History.PeripheralId
        this.selectedRendererId = defaultSetting.defaultSetting().Assembly.Renderer.History.RendererId
        this.printerList = response
      })
    },
    getRenderer() {
      renderer.list(true, renderer.Dataset.AssemblyUnitHistory).then(response => {
        this.rendererList = response
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>

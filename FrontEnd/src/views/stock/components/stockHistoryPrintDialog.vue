<template>
  <div class="stock-history-print-dialog">
    <el-dialog
      title="Stock History Print"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="onOpen"
    >
      <p>asdasdasads</p>
      <span slot="footer" class="dialog-footer">
        <el-select v-model="selectedLabelId">
          <el-option v-for="item in label" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
        <el-select v-model="selectedPrinterId">
          <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
        <el-button type="primary" @click="print">Print</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

import * as defaultSetting from '@/utils/defaultSetting'

import Stock from '@/api/stock'
const stock = new Stock()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Print from '@/api/print'
const print = new Print()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

export default {
  props: {
    stockHistoryCode: { type: String, default: '' },
    visible: { type: Boolean, default: false }
  },
  emits: ['change'],
  data() {
    return {
      data: {},
      label: null,
      printer: {},
      selectedPrinterId: 0,
      selectedLabelId: 0
    }
  },
  mounted() {
    this.getPrinter()
    this.getLabel()
  },
  methods: {
    onOpen() {
      stock.item.history.item(this.$props.stockHistoryCode).then(response => {
        this.data = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    },
    print() {
      print.template.partNote(this.selectedPrinterId, [this.data], this.selectedWorkOrderNumber).then(response => {
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPrinter() {
      peripheral.list(peripheral.Type.Printer).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
        this.selectedLabelId = defaultSetting.defaultSetting().StockLabel
        this.printer = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getLabel() {
      renderer.list(true, renderer.Dataset.Stock).then(response => {
        this.label = response
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

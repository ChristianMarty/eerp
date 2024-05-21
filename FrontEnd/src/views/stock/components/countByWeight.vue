<template>
  <div class="scale-container">
    <el-dialog
      title="Count by weight"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="onOpen"
    >
      <el-form label-width="150px">
        <el-form-item label="Selected scale:">
          <el-select v-model="selectedScaleId">
            <el-option v-for="item in scales" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
          </el-select>
        </el-form-item>

        <el-form-item label="Calibration:">

          <table class="scale">
            <tr class="scale">
              <th class="scale">Quantity</th>
              <th class="scale">Weight</th>
              <th class="scale">Weight per piece</th>
              <th class="scale" />
            </tr>
            <tr class="scale">
              <td class="scale">1</td>
              <td class="scale">{{ readings[1] }}</td>
              <td class="scale">{{ readings[1] }}</td>
              <td class="scale">
                <el-button type="primary" @click="readScale(1)">Read</el-button>
              </td>
            </tr>
            <tr class="scale">
              <td class="scale">5</td>
              <td class="scale">{{ readings[5] }}</td>
              <td class="scale">{{ readings[5] / 5 }}</td>
              <td class="scale">
                <el-button type="primary" @click="readScale(5)">Read</el-button>
              </td>
            </tr>
            <tr class="scale">
              <td class="scale">10</td>
              <td class="scale">{{ readings[10] }}</td>
              <td class="scale">{{ readings[10] / 10 }}</td>
              <td class="scale">
                <el-button type="primary" @click="readScale(10)">Read</el-button>
              </td>
            </tr>
            <tr class="scale">
              <td class="scale">20</td>
              <td class="scale">{{ readings[20] }}</td>
              <td class="scale">{{ readings[20] / 20 }}</td>
              <td class="scale">
                <el-button type="primary" @click="readScale(20)">Read</el-button>
              </td>
            </tr>
          </table>
          <p><b>Calibrated Weight:</b> {{ calibratedWeightPerPiece }}</p>
          <el-button type="danger" @click="clear()">Clear</el-button>
          <el-button type="primary" @click="saveCalibration()">Save Calibration</el-button>
        </el-form-item>

        <el-form-item label="Part Weight:">
          {{ partData.Part.SinglePartWeight }}
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="readScale()">Read Scale</el-button>
        </el-form-item>

        <el-form-item label="Scale Reading:">
          {{ reading }}
        </el-form-item>

        <el-form-item label="Quantity:">
          {{ quantity }}
        </el-form-item>

        <hr>
        <el-form-item label="Stock Item:">
          {{ partData.ItemCode }}
        </el-form-item>

        <el-form-item label="Old Quantity:">
          {{ partData.Quantity.Quantity }}
        </el-form-item>

        <el-form-item label="Counted Quantity:">
          <el-input-number
            v-model="newQuantity.Quantity"
            :min="0"
            :max="1000000"
          />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="newQuantity.Note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveQuantity">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

import Stock from '@/api/stock'
const stock = new Stock()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import * as defaultSetting from '@/utils/defaultSetting'

export default {
  components: {},
  props: { item: { type: String, default: '' }, visible: { type: Boolean, default: false }},
  data() {
    return {
      scales: [],
      selectedScaleId: defaultSetting.defaultSetting().StockCountScale.PeripheralId,
      reading: 0,
      readings: [],
      calibratedWeightPerPiece: 0,
      quantity: 0,
      partData: Object.assign({}, stock.item.itemDataEmpty),
      newQuantity: Object.assign({}, stock.item.countParameter)
    }
  },
  async mounted() {
    this.getScales()
  },
  methods: {
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    onOpen() {
      this.getStockItem(this.item)
    },
    getScales() {
      peripheral.list(peripheral.Type.Scale).then(response => {
        this.scales = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getStockItem(ItemCode) {
      stock.item.get(ItemCode).then(response => {
        if (response.length === 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.partData = response
        }
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    saveQuantity() {
      this.newQuantity.ItemCode = this.partData.ItemCode
      stock.item.count(this.newQuantity).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    saveCalibration() {
      manufacturerPart.PartNumber.saveWeight(this.partData.Part.ManufacturerPartNumberId, this.calibratedWeightPerPiece).then(response => {
        this.getStockItem(this.item)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    clear() {
      this.readings = []
      this.$forceUpdate()
    },
    readScale(quantity) {
      peripheral.scale.read(this.selectedScaleId).then(response => {

        if (quantity === undefined) {
          this.reading = response.value

          if (this.calibratedWeightPerPiece === 0){
            this.quantity = this.reading / this.partData.Part.SinglePartWeight
          } else {
            this.quantity = this.reading / this.calibratedWeightPerPiece
          }
          this.newQuantity.Quantity = Math.round(this.quantity)
        } else {
          this.readings[quantity] = response.value

          this.calibratedWeightPerPiece = 0
          let numberOfItems = 0
          if (this.readings[1] !== undefined) {
            this.calibratedWeightPerPiece += this.readings[1]
            numberOfItems++
          }
          if (this.readings[5] !== undefined) {
            this.calibratedWeightPerPiece += this.readings[5] / 5
            numberOfItems++
          }
          if (this.readings[10] !== undefined) {
            this.calibratedWeightPerPiece += this.readings[10] / 10
            numberOfItems++
          }
          if (this.readings[20] !== undefined) {
            this.calibratedWeightPerPiece += this.readings[20] / 20
            numberOfItems++
          }
          this.calibratedWeightPerPiece = this.calibratedWeightPerPiece / numberOfItems
        }
        this.$forceUpdate()
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

<style>
table.scale, th.scale, tr.scale, td.scale {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>


<template>
  <div class="stock-item-edit-dialog">
    <el-dialog
      :title="formData.StockCode"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" :model="formData" label-width="150px">
        <el-form-item label="Lot Number">
          <el-input v-model="formData.LotNumber" />
        </el-form-item>
        <el-form-item label="Date Code:">
          <el-date-picker
            v-model="formData.Date"
            type="week"
            format="yyyy Week WW"
            value-format="yyyy-MM-dd"
            placeholder="Pick a week"
          />
        </el-form-item>

        <el-form-item label="Country">
          <el-select v-model="formData.CountryOfOriginNumericCode" placeholder="Country" filterable>
            <el-option
              v-for="item in countries"
              :key="item.NumericCode"
              :label="item.Alpha2Code + ' - '+item.ShortName"
              :value="item.NumericCode"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Weight">
          <span>The weight is specified per Manufacturer Part Number and can not be updated here-</span>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save()">Save</el-button>
          <el-button @click="closeDialog()">Cancel</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script>

import Stock from '@/api/stock'
const stock = new Stock()

import Country from '@/api/country'
const country = new Country()

export default {
  name: 'StockEdit',
  props: {
    stockCode: { type: String, default: null },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      countries: [],
      partData: Object.assign({}, stock.item.itemDataEmpty),
      formData: Object.assign({}, stock.item.itemEditDataEmpty)
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.formData = Object.assign({}, stock.item.itemEditDataEmpty)
      this.countries = await country.search()
      stock.item.get(this.$props.stockCode).then(response => {
        this.partData = response
        this.formData.StockCode = this.partData.ItemCode
        this.formData.LotNumber = this.partData.LotNumber
        this.formData.Date = this.partData.Date
        this.formData.CountryOfOriginNumericCode = this.partData.CountryOfOrigin.NumericCode
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    save() {
      stock.item.edit(this.formData).then(response => {
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
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>

<template>
  <div class="app-container">

    <el-form>
      <el-form-item label="CSV:">
        <el-input v-model="csv" type="textarea" placeholder="Insert CSV data" />
      </el-form-item>
      <el-form-item label="Analyze Options:">
        <el-select v-model="analyzePath">
          <el-option
            v-for="item in analyzeOptions"
            :key="item"
            :label="item.Title"
            :value="item.Path"
          />
        </el-select>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="onSubmit()">Analyse</el-button>
      </el-form-item>
    </el-form>

    <p>
      Build Quantity:
      <el-input-number
        v-model="buildQuantity"
        :min="1"
        :max="1000"
        @change="onQuantityChange"
      />
    </p>
    <!--<el-button
      v-permission="['bom.print']"
      type="primary"
      plain
      icon="el-icon-printer"
      style="float: right;"
      @click="onPrint"
    >Print</el-button>-->

    <el-table
      :data="bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
      border
    ><el-table-column prop="ProductionPartBarcode" label="Part No" width="150" sortable>
       <template slot-scope="{ row }">
         <router-link
           :to="'/productionPart/item/' + row.ProductionPartBarcode"
           class="link-type"
         >
           <span>{{ row.ProductionPartBarcode }}</span>
         </router-link>
       </template>
     </el-table-column>
      <el-table-column prop="ReferenceDesignator" label="RefDes" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Quantity" label="Quantity" width="100" />
      <el-table-column prop="TotalQuantity" label="Total" width="100" />
      <el-table-column prop="Value" label="Description from CSV" />
      <el-table-column prop="StockQuantity" label="Stock" width="100" />
      <el-table-column prop="Name" label="Manufacturer Parts" />

      <el-table-column prop="ReferencePriceMinimum" label="Ref. Price Min." width="130" />
      <el-table-column prop="ReferencePriceWeightedAverage" label="Ref. Price Avg." width="130" />
      <el-table-column prop="ReferencePriceMaximum" label="Ref. Price Max." width="130" />

      <el-table-column prop="PurchasePriceWeightedAverage" label="Purch. Price Avg." width="150" />
      <el-table-column prop="ReferenceLeadTimeWeightedAverage" label="Ref. Lead Time" width="130" />

    </el-table>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import BillOfMaterial from '@/api/billOfMaterial'
const billOfMaterial = new BillOfMaterial()

export default {
  name: 'BomView',
  components: {},
  directives: { permission },
  data() {
    return {
      csv: null,
      bom: null,
      buildQuantity: 1,
      analyzeOptions: null,
      analyzePath: ''
    }
  },
  mounted() {
    this.getAnalyzeOptions()
  },
  methods: {
    tableAnalyzer({ row, rowIndex }) {
      if (String(row.ProductionPartBarcode).includes('Unknown')) {
        return 'error-row'
      } else if (row.TotalQuantity > row.StockQuantity) {
        return 'warning-row'
      }
      return ''
    },
    onQuantityChange() {
      this.bom.forEach(
        row => (row.TotalQuantity = row.Quantity * this.buildQuantity)
      )
    },
    onSubmit() {
      billOfMaterial.analyze(this.analyzePath, this.csv, this.buildQuantity, false).then(response => {
        this.bom = response
        this.onQuantityChange()
      })
    },
    getAnalyzeOptions() {
      billOfMaterial.getAnalyzeOptions().then(response => {
        this.analyzeOptions = response
      })
    }
  }
}
</script>

<style>
.el-table .warning-row {
  background: oldlace;
}
.el-table .error-row {
  background: Lavenderblush;
}
</style>

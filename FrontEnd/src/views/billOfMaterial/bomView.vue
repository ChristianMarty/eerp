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
    <el-button
      v-permission="['bom.print']"
      type="primary"
      plain
      icon="el-icon-printer"
      style="float: right;"
      @click="onPrint"
    >Print</el-button>

    <el-table
      :data="bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
      border
    ><el-table-column prop="ProductionPartNumber" label="Part No" width="150" sortable>
       <template slot-scope="{ row }">
         <router-link
           :to="'/productionPart/item/' + row.ProductionPartNumber"
           class="link-type"
         >
           <span>{{ row.ProductionPartNumber }}</span>
         </router-link>
       </template>
     </el-table-column>
      <el-table-column prop="ReferenceDesignator" label="RefDes" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Quantity" label="Quantity" width="100" />
      <el-table-column prop="TotalQuantity" label="Total" width="100" />
      <el-table-column prop="Value" label="Description from CSV" />
      <el-table-column prop="Stock" label="Stock" width="100" />
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
import requestBN from '@/utils/requestBN'
import permission from '@/directive/permission/index.js'

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
      if (String(row.PartNo).includes('Unknown')) {
        return 'error-row'
      } else if (row.TotalQuantity > row.Stock) {
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
      requestBN({
        method: 'post',
        url: this.analyzePath,
        data: { csv: this.csv, BuildQuantity: this.buildQuantity }
      }).then(response => {
        this.bom = response.data
        this.onQuantityChange()
      })
    },
    getAnalyzeOptions() {
      requestBN({
        method: 'get',
        url: '/billOfMaterial/analyze'
      }).then(response => {
        this.analyzeOptions = response.data
      })
    },
    onPrint() {
      requestBN({
        method: 'post',
        url: '/print/bonPrint',
        data: { data: this.bom, PrinterId: 2 }
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

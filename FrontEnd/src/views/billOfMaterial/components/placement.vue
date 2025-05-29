<template>
  <div class="placement-container">

    <el-table
      :data="stockBom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNumber" label="Part No" width="120" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="ReferenceDesignator" label="Ref Des" width="150" sortable />
      <el-table-column prop="Layer" label="Layer" width="150" sortable />
      <el-table-column prop="PositionX" label="Position X" width="150" sortable />
      <el-table-column prop="PositionY" label="Position Y" width="150" sortable />
      <el-table-column prop="Rotation" label="Rotation" width="150" sortable />
    </el-table>
  </div>
</template>

<script>
import BillOfMaterial from '@/api/billOfMaterial'
const billOfMaterial = new BillOfMaterial()

export default {
  props: { revisionId: { type: Number, default: 0 }},
  data() {
    return {
      stockBom: null
    }
  },
  mounted() {
    this.getData()
  },
  methods: {
    getData() {
      billOfMaterial.item.placement(this.$props.revisionId).then(response => {
        this.stockBom = response
      })
    }
  }
}
</script>

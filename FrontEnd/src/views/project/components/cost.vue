<template>
  <div class="availability-container">

    <el-table
      :data="stockBom.Bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNo" label="Part No" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/prodParts/prodPartView/' + row.ProductionPartNo"
            class="link-type"
          >
            <span>{{ row.ProductionPartNo }}</span>
          </router-link>
        </template>
      </el-table-column>¨

    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { projectId: { type: Number, default: 0 }},
  data() {
    return {
      stockBom: null
    }
  },
  mounted() {
    this.getBomStock()
  },
  methods: {
    getBomStock() {
      requestBN({
        url: '/project/availability',
        methood: 'get',
        params: {
          ProjectId: this.$props.projectId
        }
      }).then(response => {
        this.stockBom = response.data
      })
    }
  }
}
</script>

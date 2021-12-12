<template>
  <div class="bom-upload-container">
    <el-input v-model="bomData" type="textarea" placeholder="Insert CSV data" />
    <el-button type="primary" @click="analyse">Analyse</el-button>

    <el-table
      :data="bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    ><el-table-column prop="PartNo" label="Part No" width="150" sortable>
       <template slot-scope="{ row }">
         <router-link
           :to="'/prodParts/prodPartView/' + row.PartNo"
           class="link-type"
         >
           <span>{{ row.PartNo }}</span>
         </router-link>
       </template>
     </el-table-column>
      <el-table-column prop="RefDes" label="RefDes" />
      <el-table-column prop="Quantity" label="Quantity" width="100" />
      <el-table-column prop="Value" label="Description from CSV" />
      <el-table-column prop="Stock" label="Stock" width="100" />
    </el-table>
    <el-button type="primary" @click="save">Save</el-button>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { projectId: { type: Number, default: 0 }},
  data() {
    return {
      bom: null,
      bomData: ''
    }
  },
  mounted() {
    this.getBomStock()
  },
  methods: {
    save() {
      requestBN({
        method: 'post',
        url: '/project/save',
        data: { Bom: this.bom, ProjectId: this.$props.projectId }
      }).then(response => {

      })
    },
    analyse() {
      requestBN({
        method: 'post',
        url: '/project/analyze/analyze_Target3001',
        data: { csv: this.bomData }
      }).then(response => {
        this.bom = response.data.bom
      })
    }
  }
}
</script>

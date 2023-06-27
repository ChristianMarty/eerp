<template>
  <div class="bom-upload-container">
    <el-input v-model="bomData" type="textarea" placeholder="Insert CSV data" />
    <el-select v-model="analyzePath">
      <el-option
        v-for="item in analyzeOptions"
        :key="item"
        :label="item.Title"
        :value="item.Path"
      />
    </el-select>
    <el-button type="primary" @click="analyse">Analyse</el-button>

    <el-table
      :data="bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    ><el-table-column prop="ProductionPartNumber" label="Part No" width="150" sortable>
       <template slot-scope="{ row }">
         <router-link
           :to="'/prodParts/prodPartView/' + row.ProductionPartNumber"
           class="link-type"
         >
           <span>{{ row.ProductionPartNumber }}</span>
         </router-link>
       </template>
     </el-table-column>
      <el-table-column prop="ReferenceDesignator" label="RefDes" />
      <el-table-column prop="Quantity" label="Quantity" width="100" />
      <el-table-column prop="Description" label="Description from CSV" />
    </el-table>
    <el-button type="primary" @click="save">Save</el-button>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { revisionId: { type: Number, default: 0 }},
  data() {
    return {
      bom: null,
      bomData: '',
      analyzeOptions: null,
      analyzePath: ''
    }
  },
  mounted() {
    this.getAnalyzeOptions()
  },
  methods: {
    save() {
      requestBN({
        method: 'post',
        url: '/billOfMaterial/bom',
        data: { Bom: this.bom, RevisionId: this.$props.revisionId }
      }).then(response => {

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
    analyse() {
      requestBN({
        method: 'post',
        url: this.analyzePath,
        data: { csv: this.bomData }
      }).then(response => {
        this.bom = response.data
      })
    }
  }
}
</script>

<template>
  <div class="bom-upload-container">
    <el-input v-model="bomData" type="textarea" placeholder="Insert CSV data" />
    <el-button type="primary" @click="analyse">Analyse</el-button>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { projectNo: { type: Number, default: 0 }},
  data() {
    return {
      stockBom: null,
      bomData: ''
    }
  },
  mounted() {
    this.getBomStock()
  },
  methods: {
    getBomStock() {
      requestBN({
        url: '/project/stock',
        methood: 'get',
        params: {
          ProjectId: this.$props.projectNo
        }
      }).then(response => {
        this.stockBom = response.data
      })
    },
    analyse() {

    }
  }
}
</script>

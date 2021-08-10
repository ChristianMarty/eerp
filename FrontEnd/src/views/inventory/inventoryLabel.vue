<template>
  <div class="app-container">
    <h1>Inventory Label</h1>
    <el-divider />

    <ul>
      <li v-for="i in invList">{{ i }}</li>
    </ul>

    <el-button type="primary" @click="clearList">Clear List</el-button>

    <el-divider />
    <h2>Print Preview</h2>
    <div class="preview-container">
      <a :href="printPreviewPath" target="print" style="float: right;">
        <el-button type="primary" plain icon="el-icon-printer">Print</el-button>
      </a>

      <el-form :inline="true" :model="form">
        <el-form-item label="Offset" />
        <el-input-number
          v-model="offset"
          :min="0"
          :max="47"
          @change="handleChange"
        />
      </el-form>
      <div style="height:297mm;">
        <iframe :src="printPreviewPath" width="100%" height="100%" />
      </div>
    </div>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import Cookies from 'js-cookie'

const printPath =
  process.env.VUE_APP_BLUENOVA_BASE + '/apiFunctions/inventory/labelPage.php'

export default {
  name: 'InventoryView',
  components: {},
  data() {
    return {
      inventoryData: null,
      offset: 0,
      form: null,
      printPreviewPath: null,
      invList: null
    }
  },
  mounted() {
    //  this.getInventoryData();
    this.loadInventoryList()
  },
  created() {},
  methods: {
    getInventoryData() {
      requestBN({
        url: '/inventory',
        methood: 'get',
        params: { InvNo: this.$route.params.invNo }
      }).then(response => {
        this.inventoryData = response.data[0]
      })
    },
    loadInventoryList() {
      try {
        var cookiesText = Cookies.get('invNo')
        this.invList = JSON.parse(cookiesText)
      } catch (e) {
        this.invList = []
      }

      this.handleChange()
    },
    handleChange() {
      this.printPreviewPath =
        printPath + '?offset=' + this.offset + '&invNo=' + this.invList
    },
    clearList() {
      Cookies.remove('invNo')
      this.loadInventoryList()
    },
    print() {
      window.open(this.printPreviewPath)
    }
  }
}
</script>

<style scoped>
.preview-container {
  height: 100vh;
  width: 210mm;
}
</style>

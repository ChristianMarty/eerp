<template>
  <div class="app-container">
    <h1>Inventory Label</h1>
    <el-divider />

    <ul>
      <li v-for="value in invList" :key="value">{{ value }}</li>
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

import Cookies from 'js-cookie'

import Inventory from '@/api/inventory'
const inventory = new Inventory()

const printPath =
  process.env.VUE_APP_BLUENOVA_BASE + '/renderer.php/inventoryLabelPage'

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
    async getInventoryData() {
      this.inventoryData = await inventory.search({ InventoryNumber: this.$route.params.invNo })[0]
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

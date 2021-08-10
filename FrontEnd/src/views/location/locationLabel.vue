<template>
  <div class="app-container">
    <h1>Location Label</h1>
    <p>Use Avery Zweckform L4773</p>
    <el-divider />

    <el-form :inline="true" :model="form">
      <el-form-item label="Offset" />
      <el-input-number
        v-model="offset"
        :min="0"
        :max="23"
        @change="handleChange"
      />
    </el-form>

    <ul>
      <li v-for="i in invList">{{ i }}</li>
    </ul>

    <el-button type="primary" @click="clearList">Clear List</el-button>
    <el-divider />

    <div class="preview-container">
      <h2 style="float: left;">Print Preview</h2>
      <a :href="printPreviewPath" target="print" style="float: right;">
        <el-button type="primary" plain icon="el-icon-printer">Print</el-button>
      </a>
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
  process.env.VUE_APP_BLUENOVA + 'apiFunctions/location/locationLabelPage.php'

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
    this.getInventoryData()
    this.loadInvList()
  },
  created() {},
  methods: {
    getInventoryData() {
      requestBN({
        url: '/inventory/',
        methood: 'get',
        params: { InvNo: this.$route.params.invNo }
      }).then(response => {
        this.inventoryData = response.data[0]
      })
    },
    loadInvList() {
      try {
        var cookiesText = Cookies.get('locNo')
        this.invList = JSON.parse(cookiesText)
      } catch (e) {
        this.invList = []
      }

      this.handleChange()
    },
    handleChange() {
      this.printPreviewPath =
        printPath + '?offset=' + this.offset + '&locNo=' + this.invList
    },
    clearList() {
      Cookies.remove('locNo')
      this.loadInvList()
    },
    print() {
      console.log(this.printPreviewPath)
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

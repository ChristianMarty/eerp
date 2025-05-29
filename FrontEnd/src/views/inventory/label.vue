<template>
  <div class="app-container">
    <h1>Inventory Label</h1>
    <el-divider />

    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputInventoryNumber"
        placeholder="Please input inventory number"
        @keyup.enter.native="addItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="addItem"
        />
      </el-input>
    </p>
    <p>
      <el-table :data="itemList" border style="width: 100%">
        <el-table-column prop="ItemCode" label="Inventory Number" width="180" />
        <el-table-column prop="Description" label="Description">
          <template slot-scope="{ row }">
            {{ row.Title }} - {{ row.ManufacturerName }} {{ row.Type }}
          </template>
        </el-table-column>
      </el-table>
    </p>

    <el-button type="primary" @click="clearList">Clear List</el-button>
    <el-divider />

    <h2>Print Preview</h2>
    <div class="preview-container">
      <a :href="printPreviewPath" target="print" style="float: right;">
        <el-button type="primary" plain icon="el-icon-printer">Print</el-button>
      </a>

      <el-form :inline="true">

        <el-form-item label="Offset" />
        <el-input-number
          v-model="offset"
          :min="0"
          :max="47"
          @change="handleChange"
        />

        <el-select
          v-model="rendererSelected"
          placeholder="Select Document"
          style="min-width: 200px; margin-left: 10px;"
        >
          <el-option
            v-for="item in rendererList"
            :key="item.Id"
            :label="item.Name"
            :value="item.Id"
          />
        </el-select>

      </el-form>
      <p>{{ rendererSelected.Description }}</p>
      <div style="height:297mm;">
        <iframe :src="printPreviewPath" width="100%" height="100%" />
      </div>
    </div>
  </div>
</template>

<script>

import Inventory from '@/api/inventory'
const inventory = new Inventory()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

export default {
  name: 'InventoryView',
  components: {},
  data() {
    return {
      offset: 0,
      printPreviewPath: null,
      itemList: [],

      inputInventoryNumber: '',
      rendererList: [],
      rendererSelected: 0
    }
  },
  mounted() {
  },
  created() {
    renderer.list(true, renderer.Dataset.InventoryItem).then(response => {
      this.rendererList = response
      this.rendererSelected = this.rendererList[0].Id
    })
  },
  methods: {
    handleChange() {
      const numberList = this.itemList.map(element => element.ItemCode)
      renderer.item(this.rendererSelected).then(response => {
        const printPath =
          process.env.VUE_APP_BLUENOVA_BASE + '/renderer.php/' + response.Code

        this.printPreviewPath =
          printPath + '?Offset=' + this.offset + '&InventoryNumber=' + numberList
      })
    },
    addItem() {
      inventory.item(this.inputInventoryNumber, false).then(response => {
        this.itemList.push(response)
        this.inputInventoryNumber = ''
        this.handleChange()
      })
    },
    clearList() {
      this.itemList = []
      this.handleChange()
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

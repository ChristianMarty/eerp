<template>
  <div class="app-container">
    <h1>Location Label</h1>
    <el-divider />

    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputLocationNumber"
        placeholder="Please input location number"
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
        <el-table-column prop="LocationNumber" label="Location Number" width="180" />
        <el-table-column prop="DisplayName" label="Name" />
        <el-table-column prop="Title" label="Title" />
        <el-table-column prop="Description" label="Description" />
      </el-table>
    </p>

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

        <el-select
          v-model="rendererSelected"
          placeholder="Select Document"
          style="min-width: 200px; margin-left: 10px;"
        >
          <el-option
            v-for="item in rendererList"
            :key="item.Id"
            :label="item.Name"
            :value="item"
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

import Print from '@/api/print'
const print = new Print()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Loaction from '@/api/location'
const loaction = new Loaction()

export default {
  name: 'InventoryView',
  components: {},
  data() {
    return {
      offset: 0,
      printPreviewPath: null,
      itemList: [],

      inputLocationNumber: '',
      rendererList: [],
      rendererSelected: null
    }
  },
  mounted() {
    this.handleChange()
  },
  created() {
    print.label.search('LocationLabel').then(response => {
      this.rendererList = response
      this.rendererSelected = this.rendererList[0]
      this.handleChange()
    }).catch(response => {
      this.showErrorMessage(response)
    })
  },
  methods: {

    handleChange() {
      const numberList = this.itemList.map(element => element.ItemCode)
      renderer.item(this.rendererSelected.Id).then(response => {
        const printPath =
          process.env.VUE_APP_BLUENOVA_BASE + '/renderer.php/' + response.Code

        this.printPreviewPath =
          printPath + '?Offset=' + this.offset + '&LocationNumber=' + numberList
      }).catch(response => {
        this.showErrorMessage(response)
      })
    },
    addItem() {
      loaction.item.get(this.inputLocationNumber, false).then(response => {
        this.itemList.push(response)
        this.inputLocationNumber = ''
        this.handleChange()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
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
